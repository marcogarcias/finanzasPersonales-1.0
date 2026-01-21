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
                                    ->where('anio', $year)
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
            ->where('anio', $year)
            ->whereIn('mes', $months)
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
            $comprobantes = \App\Models\Comprobante::where('user_id', $userId)
                ->where($targetColumn, $rfc)
                ->where('clase_comprobante', $clase)
                ->where('anio', $year)
                ->whereIn('mes', (array)$months)
                ->orderBy('fecha', 'desc')
                ->get();

            // 3. Crear archivo CSV manualmente
            $tempPath = storage_path('app/temp_export_' . $userId . '.csv');
            $file = fopen($tempPath, 'w');

            // Añadir BOM para que Excel reconozca UTF-8 (acentos y ñ)
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Encabezados
            fputcsv($file, [
                'UUID', 'Fecha', 'Folio', 'RFC Emisor', 'Nombre Emisor', 
                'RFC Receptor', 'Nombre Receptor', 'Clase', 'Tipo', 
                'Moneda', 'Subtotal', 'Descuento', 'IVA 16%', 'Total', 'Conceptos'
            ]);

            // Datos
            foreach ($comprobantes as $c) {
                fputcsv($file, [
                    $c->uuid,
                    $c->fecha ? $c->fecha->format('Y-m-d H:i:s') : 'S/F',
                    ($c->serie ?? '') . ($c->folio ?? ''),
                    $c->rfc_emisor,
                    $c->nombre_emisor,
                    $c->rfc_receptor,
                    $c->nombre_receptor,
                    $c->clase_comprobante,
                    $c->tipo_comprobante,
                    $c->moneda,
                    $c->subtotal,
                    $c->descuento,
                    $c->iva_16,
                    $c->total,
                    $c->conceptos,
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
