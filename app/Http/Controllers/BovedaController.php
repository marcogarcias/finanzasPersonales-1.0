<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;
use Native\Laravel\Dialog;
use PhpCfdi\SatEstadoCfdi\Consumer;
use App\Services\HttpSatConsumerClient;

class BovedaController extends Controller
{
    /**
     * Vista principal
     */
    public function index()
    {
        return view('boveda.index');
    }

    /**
     * API: Escanear niveles desde la Base de Datos (RFC -> Clase -> Año -> Mes)
     */
    public function scan(Request $request)
    {
        $level = $request->query('level', 'rfc');
        $userId = auth()->id();
        
        $query = \App\Models\Comprobante::where('user_id', $userId);
        $results = [];

        try {
            switch ($level) {
                case 'rfc':
                    // El RFC de la bóveda es el emisor en 'emitidos' O el receptor en 'recibidos'
                    $emitidos = (clone $query)->where('clase_comprobante', 'emitido')->distinct()->pluck('rfc_emisor');
                    $recibidos = (clone $query)->where('clase_comprobante', 'recibido')->distinct()->pluck('rfc_receptor');
                    
                    $results = $emitidos->merge($recibidos)->unique()->sort()->values();
                    break;

                case 'type':
                    $rfc = $request->query('rfc');
                    // Ver si para este RFC hay emitidos o recibidos
                    $hasEmitidos = (clone $query)->where('clase_comprobante', 'emitido')->where('rfc_emisor', $rfc)->exists();
                    $hasRecibidos = (clone $query)->where('clase_comprobante', 'recibido')->where('rfc_receptor', $rfc)->exists();
                    
                    if ($hasEmitidos) $results[] = 'emitido';
                    if ($hasRecibidos) $results[] = 'recibido';
                    break;

                case 'year':
                    $rfc = $request->query('rfc');
                    $clase = $request->query('type');
                    
                    $targetColumn = ($clase === 'emitido') ? 'rfc_emisor' : 'rfc_receptor';
                    
                    $results = $query->where('clase_comprobante', $clase)
                                    ->where($targetColumn, $rfc)
                                    ->selectRaw("strftime('%Y', fecha) as anio")
                                    ->distinct()
                                    ->orderBy('anio', 'desc')
                                    ->pluck('anio');
                    break;
                
                case 'month':
                    $rfc = $request->query('rfc');
                    $clase = $request->query('type');
                    $year = $request->query('year');
                    
                    $targetColumn = ($clase === 'emitido') ? 'rfc_emisor' : 'rfc_receptor';

                    $results = $query->where('clase_comprobante', $clase)
                                    ->where($targetColumn, $rfc)
                                    ->whereYear('fecha', $year)
                                    ->selectRaw("strftime('%m', fecha) as mes")
                                    ->distinct()
                                    ->orderBy('mes', 'asc')
                                    ->pluck('mes');
                    break;
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json($results);
    }

    /**
     * API: Obtener registros de Comprobantes filtrados
     */
    public function getFiles(Request $request)
    {
        $userId = auth()->id();
        $rfc = $request->query('rfc');
        $clase = $request->query('type');
        $year = $request->query('year');
        $months = $request->query('months', []);

        if (!is_array($months)) {
            $months = [$months];
        }

        $targetColumn = ($clase === 'emitido') ? 'rfc_emisor' : 'rfc_receptor';

        $comprobantes = \App\Models\Comprobante::where('user_id', $userId)
            ->where($targetColumn, $rfc)
            ->where('clase_comprobante', $clase)
            ->whereYear('fecha', $year)
            ->where(function($q) use ($months) {
                foreach ($months as $m) {
                    $q->orWhereMonth('fecha', $m);
                }
            })
            ->orderBy('fecha', 'desc')
            ->get();

        // Mapear a formato básico para la tabla
        $formatted = $comprobantes->map(function($c) {
            return [
                'id' => $c->id,
                'uuid' => $c->uuid,
                'folio' => ($c->serie ?? '') . ($c->folio ?? ''),
                'emisor' => $c->nombre_emisor ?? $c->rfc_emisor,
                'fecha' => $c->fecha->format('Y-m-d H:i'),
                'total' => '$' . number_format($c->total, 2),
                'moneda' => $c->moneda,
                'tipo' => $c->tipo_comprobante,
                'estado' => $c->estado_sat,
                'xml_name' => $c->archivo_xml
            ];
        });

        return response()->json($formatted);
    }

    /**
     * API: Exportar los resultados actuales a CSV (Manual para evitar dependencias)
     */
    public function export(Request $request)
    {
        $userId = auth()->id();
        $rfc = $request->query('rfc');
        $clase = $request->query('type');
        $year = $request->query('year');
        $months = $request->query('months', []);

        // 1. Preguntar donde guardar usando NativePHP Dialog
        $path = app(Dialog::class)
            ->title('Guardar Reporte de Comprobantes')
            ->defaultPath('Reporte_CFDI_' . date('Y-m-d_His') . '.csv')
            ->filter('CSV', ['csv'])
            ->save();

        if (!$path) {
            return response()->json(['message' => 'Exportación cancelada'], 200);
        }

        try {
            // 2. Obtener datos filtrados
            $targetColumn = ($clase === 'emitido') ? 'rfc_emisor' : 'rfc_receptor';
            $comprobantes = \App\Models\Comprobante::leftJoin('proveedores', function($join) use ($userId) {
                    $join->on('comprobantes.rfc_emisor', '=', 'proveedores.rfc')
                         ->on('comprobantes.rfc_receptor', '=', 'proveedores.rfc_receptor')
                         ->where('proveedores.user_id', '=', $userId);
                })
                ->where('comprobantes.user_id', $userId)
                ->where('comprobantes.' . $targetColumn, $rfc)
                ->where('comprobantes.clase_comprobante', $clase)
                ->whereYear('comprobantes.fecha', $year)
                ->where(function($q) use ($months) {
                    foreach ((array)$months as $m) {
                        $q->orWhereMonth('comprobantes.fecha', $m);
                    }
                })
                ->select([
                    'comprobantes.*',
                    'proveedores.tipo_de_uso as prov_tipo_de_uso',
                    'proveedores.efecto_fiscal as prov_efecto_fiscal',
                    'proveedores.momento_fiscal as prov_momento_fiscal',
                    'proveedores.categoria as prov_categoria',
                ])
                ->orderBy('comprobantes.fecha', 'desc')
                ->get();

            // 3. Crear archivo CSV manualmente
            $tempPath = storage_path('app/temp_export_' . $userId . '.csv');
            $file = fopen($tempPath, 'w');

            // Añadir BOM para que Excel reconozca UTF-8 (acentos y ñ)
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Encabezados
            fputcsv($file, [
                // INICIO. COLUMNAS CALCULADAS
                'Importe Neto', 'Tasa IVA (s/reporte)', 'Tasa IVA final', 
                'Base IVA 0%', 'Base IVA 16%', 'Comprobación vs Total Factura', 
                'Comprobación vs Importe Neto',
                // FIN. COLUMNAS CALCULADAS

                // INICIO. COLUMNAS DEL XML
                'UUID', 'UUID Relacionado', 'Tipo Relación', 'Estado SAT', 'Versión', 
                'Tipo Comprobante', 'Fecha', 'Fecha Timbrado', 'Serie', 'Folio', 
                'RFC Emisor', 'Nombre Emisor', 'Regimen Fiscal Emisor', 'Lugar De Expedición', 
                'RFC Receptor', 'Nombre Receptor', 'Residencia Fiscal', 'Uso CFDI', 
                'Regimen Fiscal Receptor', 'Subtotal', 'Descuento', 'Total IEPS', 
                'IVA 16%', 'Retenido IVA', 'Retenido ISR', 'ISH', 'Total', 
                'Total Traslados', 'Total Retenidos', 'Total Local Traslado', 
                'Total Local Retenido', 'Complemento', 'Moneda', 'Tipo Cambio', 
                'Forma Pago', 'Método Pago', 'Conceptos', 'Combustible', 
                'IEPS 3%', 'IEPS 6%', 'IEPS 7%', 'IEPS 8%', 'IEPS 9%', 'IEPS 26.5%', 
                'IEPS 30%', 'IEPS 53%', 'IEPS 160%', 'Archivo XML', 'Direccion Emisor', 
                'Direccion Receptor', 
                'IVA 8%', 'IEPS 30.4%', 'IVA Ret 6%',
                // FIN. COLUMNAS DEL XML

                // INICIO. COLUMNAS CALCULADAS
                'Periodo Fiscal', 'Base Neta',
                // FIN. COLUMNAS CALCULADAS

                // INICIO. COLUMNAS DE PROVEEDORES
                'Tipo de uso', 'Efecto Fiscal', 'Momento Fiscal', 'Categoría',
                // FIN. COLUMNAS DE PROVEEDORES
            ]);

            // Datos
            foreach ($comprobantes as $c) {
                fputcsv($file, [
                    // INICIO. COLUMNAS CALCULADAS
                    $c->importe_neto = $c->subtotal - $c->descuento,
                    $c->tasa_iva = $c->iva_16 == 0 ? 0 : round($c->iva_16/$c->importe_neto, 2),
                    //$c->base_iva_0 = ($c->tasa_iva<16 ? ($c->iva_16/0.16) : 0),
                    $c->base_iva_0 = ($c->tasa_iva < 0.16 ? ($c->iva_16/0.16) : 0),
                    $c->tasa_iva_final = ($c->base_iva_0 == 0 ? 0 : round($c->iva_16/$c->base_iva_0, 2)),
                    $c->base_iva_16 = $c->importe_neto - $c->base_iva_0,
                    $c->comprobacion_vs_total = $c->importe_neto + $c->iva_16 - $c->total,
                    $c->comprobacion_vs_importe = $c->importe_neto - $c->base_iva_0 - $c->base_iva_16,
                    // FIN. COLUMNAS CALCULADAS

                    // INICIO. COLUMNAS DEL XML
                    $c->uuid,
                    $c->uuid_relacion,
                    $c->tipo_relacion,
                    $c->estado_sat,
                    $c->version,
                    $c->tipo_comprobante,
                    $c->fecha,
                    $c->fecha_timbrado,
                    $c->serie,
                    $c->folio,
                    $c->rfc_emisor,
                    $c->nombre_emisor,
                    $c->regimen_fiscal,
                    $c->lugar_expedicion,
                    $c->rfc_receptor,
                    $c->nombre_receptor,
                    $c->residencia_fiscal,
                    $c->uso_cfdi,
                    $c->regimen_fiscal_receptor,
                    $c->subtotal,
                    $c->descuento,
                    $c->total_ieps,
                    $c->iva_16,
                    $c->retenido_iva,
                    $c->retenido_isr,
                    $c->ish,
                    $c->total,
                    $c->total_traslados,
                    $c->total_retenidos,
                    $c->total_local_traslado,
                    $c->total_local_retenido,
                    $c->complemento,
                    $c->moneda,
                    $c->tipo_cambio,
                    $c->forma_pago,
                    $c->metodo_pago,
                    $c->conceptos,
                    $c->combustible,
                    $c->ieps_3,
                    $c->ieps_6,
                    $c->ieps_7,
                    $c->ieps_8,
                    $c->ieps_9,
                    $c->ieps_26_5,
                    $c->ieps_30,
                    $c->ieps_53,
                    $c->ieps_160,
                    $c->archivo_xml,
                    $c->direccion_emisor,
                    $c->direccion_receptor,
                    $c->iva_8,
                    $c->ieps_30_4,
                    $c->iva_ret_6,
                    // FIN. COLUMNAS DEL XML

                    // INICIO. COLUMNAS CALCULADAS
                    date('m', strtotime($c->fecha)),
                    $c->subtotal - $c->descuento,
                    // FIN. COLUMNAS CALCULADAS

                    // INICIO. COLUMNAS DE PROVEEDORES (tabla proveedores)
                    $c->prov_tipo_de_uso ?? '',
                    $c->prov_efecto_fiscal ?? '',
                    $c->prov_momento_fiscal ?? '',
                    $c->prov_categoria ?? '',
                    // FIN. COLUMNAS DE PROVEEDORES
                ]);
            }

            fclose($file);

            // 4. Mover a la ruta final
            if (File::exists($path)) {
                File::delete($path);
            }
            File::move($tempPath, $path);

            return response()->json([
                'status' => 'success',
                'message' => 'Reporte generado correctamente.',
                'path' => $path
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al exportar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Eliminar comprobante y archivo físico
     */
    public function destroy($id)
    {
        try {
            $comprobante = \App\Models\Comprobante::findOrFail($id);
            
            // Verificar pertenencia
            if ($comprobante->user_id !== auth()->id()) {
                return response()->json(['error' => 'No tiene permisos para eliminar este registro.'], 403);
            }

            // 1. Eliminar archivo físico si existe
            if ($comprobante->xml_path && File::exists($comprobante->xml_path)) {
                try {
                    File::delete($comprobante->xml_path);
                } catch (\Exception $fe) {
                    \Illuminate\Support\Facades\Log::warning("No se pudo eliminar el archivo físico: " . $comprobante->xml_path);
                }
            }

            // 2. Eliminar definitivamente (Force Delete)
            $comprobante->forceDelete();

            return response()->json([
                'status' => 'success', 
                'message' => 'Comprobante eliminado correctamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar: ' . $e->getMessage()], 500);
        }
    }
    /**
     * API: Eliminar múltiples comprobantes y sus archivos físicos
     */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        
        if (empty($ids)) {
            return response()->json(['error' => 'No se seleccionaron registros para eliminar.'], 400);
        }

        $userId = auth()->id();
        $comprobantes = \App\Models\Comprobante::whereIn('id', $ids)
            ->where('user_id', $userId)
            ->get();

        $deletedCount = 0;
        foreach ($comprobantes as $comprobante) {
            // 1. Eliminar archivo físico si existe
            if ($comprobante->xml_path && File::exists($comprobante->xml_path)) {
                try {
                    File::delete($comprobante->xml_path);
                } catch (\Exception $fe) {
                    \Illuminate\Support\Facades\Log::warning("No se pudo eliminar el archivo físico: " . $comprobante->xml_path);
                }
            }
            // 2. Eliminar definitivamente (Force Delete)
            $comprobante->forceDelete();
            $deletedCount++;
        }

        return response()->json([
            'status' => 'success',
            'message' => "Se eliminaron {$deletedCount} comprobantes correctamente."
        ]);
    }

    /**
     * Verifica el estatus de los comprobantes seleccionados ante el SAT.
     */
    public function bulkCheckStatus(Request $request)
    {
        $ids = $request->input('ids', []);
        $comprobantes = \App\Models\Comprobante::whereIn('id', $ids)->get();
        
        if ($comprobantes->isEmpty()) {
            return response()->json(['error' => 'No se seleccionaron comprobantes válidos.'], 400);
        }

        // Usamos nuestro cliente HTTP personalizado para evitar depender de la extensión SOAP
        $client = new HttpSatConsumerClient();
        $consumer = new Consumer($client);
        
        $updatedCount = 0;
        $totalVerified = 0;
        
        foreach ($comprobantes as $comprobante) {
            $totalVerified++;
            
            // El total debe estar formateado a 6 decimales para la consulta al SAT
            $total = number_format($comprobante->total, 6, '.', '');
            $expression = "?re={$comprobante->rfc_emisor}&rr={$comprobante->rfc_receptor}&tt={$total}&id={$comprobante->uuid}";
            
            try {
                $response = $consumer->execute($expression);
                
                // Valores posibles: Vigente, Cancelado, No Encontrado
                $nuevoEstado = strtolower($response->document()->value());
                $estadoAnterior = strtolower((string)$comprobante->estado_sat);
                
                // Solo actualizar e incrementar si el estado cambió
                if ($nuevoEstado && $nuevoEstado !== $estadoAnterior) {
                    $comprobante->estado_sat = $nuevoEstado;
                    $comprobante->save();
                    $updatedCount++;
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Error verificando estatus de CFDI {$comprobante->uuid}: " . $e->getMessage());
            }
        }
        
        return response()->json([
            'status' => 'success',
            'message' => "Se verificaron {$totalVerified} comprobantes. Cambios detectados: {$updatedCount}."
        ]);
    }
}
