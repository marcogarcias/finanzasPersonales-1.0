<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comprobante;
use App\Models\Rfc;
use Illuminate\Support\Facades\DB;

class ReporteFiscalController extends Controller
{
    /**
     * Vista de Gastos Deducibles
     */
    public function gastosDeducibles()
    {
        return view('reportes.fiscales.gastos_deducibles');
    }

    /**
     * API para cargar los filtros iniciales (RFCs, Años, etc)
     */
    public function getFiltersData(Request $request)
    {
        $userId = auth()->id();
        $level = $request->get('level', 'rfc');
        $rfc = $request->get('rfc');
        $year = $request->get('year');

        if ($level === 'rfc') {
            // Obtener todos los RFCs registrados para el usuario
            $rfcs = \App\Models\Rfc::where('user_id', $userId)
                ->select('rfc', 'razon_social')
                ->orderBy('rfc', 'asc')
                ->get();
                
            return response()->json($rfcs);
        }

        if ($level === 'year' && $rfc) {
            $years = Comprobante::where('user_id', $userId)
                ->where('rfc_receptor', $rfc)
                ->where('clase_comprobante', 'recibido')
                ->select(DB::raw('strftime("%Y", fecha) as year'))
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year');
            return response()->json($years);
        }

        if ($level === 'month' && $rfc && $year) {
            $months = Comprobante::where('user_id', $userId)
                ->where('rfc_receptor', $rfc)
                ->where('clase_comprobante', 'recibido')
                ->whereYear('fecha', $year)
                ->select(DB::raw('cast(strftime("%m", fecha) as integer) as month'))
                ->distinct()
                ->orderBy('month', 'asc')
                ->pluck('month');
            return response()->json($months);
        }

        if ($level === 'data') {
            $rfcReceptor = $request->get('rfc');
            $year = $request->get('year');
            $months = $request->get('months', []);
            $efecto = $request->get('efecto');
            $uso = $request->get('uso');

            $query = Comprobante::where('user_id', $userId)
                ->where('rfc_receptor', $rfcReceptor)
                ->whereYear('fecha', $year)
                ->whereIn(DB::raw('cast(strftime("%m", fecha) as integer)'), $months)
                ->where('clase_comprobante', 'recibido');

            // Filtrar por los datos técnicos del proveedor si se especifican
            if ($efecto || $uso) {
                $query->whereHas('proveedor', function($q) use ($efecto, $uso) {
                    if ($efecto) $q->where('efecto_fiscal', $efecto);
                    if ($uso) $q->where('tipo_de_uso', $uso);
                });
            }

            $comprobantes = $query->with(['proveedor.actividadEconomica'])->get();

            $grouped = [];
            $monthNames = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

            foreach ($comprobantes as $c) {
                $m = (int)$c->fecha->format('m');
                if (!isset($grouped[$m])) {
                    $grouped[$m] = [
                        'periodo' => "Periodo {$m} ({$monthNames[$m]} {$year})",
                        'mes_num' => $m,
                        'proveedores' => []
                    ];
                }

                $rfcEmisor = $c->rfc_emisor;
                if (!isset($grouped[$m]['proveedores'][$rfcEmisor])) {
                    $actividad = $c->proveedor?->actividadEconomica?->actividad ?? 'Sin clasificar';
                    
                    $grouped[$m]['proveedores'][$rfcEmisor] = [
                        'rfc_emisor' => $rfcEmisor,
                        'nombre_emisor' => $c->nombre_emisor,
                        'tipo_erogacion' => $actividad,
                        'base_iva_0' => 0,
                        'base_iva_16' => 0,
                        'suma_iva_16' => 0,
                        'suma_ret_iva' => 0,
                        'suma_ret_isr' => 0,
                        'total' => 0
                    ];
                }

                // Cálculo de bases similar a BovedaController
                $importe_neto = $c->subtotal - $c->descuento;
                $tasa_iva = ($c->iva_16 == 0 || $importe_neto == 0) ? 0 : round($c->iva_16 / $importe_neto, 2);
                $base_iva_0 = ($tasa_iva < 0.16) ? ($c->iva_16 / 0.16) : 0;
                $base_iva_16 = $importe_neto - $base_iva_0;

                $grouped[$m]['proveedores'][$rfcEmisor]['base_iva_0'] += (float)$base_iva_0;
                $grouped[$m]['proveedores'][$rfcEmisor]['base_iva_16'] += (float)$base_iva_16;
                $grouped[$m]['proveedores'][$rfcEmisor]['suma_iva_16'] += (float)$c->iva_16;
                $grouped[$m]['proveedores'][$rfcEmisor]['suma_ret_iva'] += (float)$c->retenido_iva;
                $grouped[$m]['proveedores'][$rfcEmisor]['suma_ret_isr'] += (float)$c->retenido_isr;
                $grouped[$m]['proveedores'][$rfcEmisor]['total'] += (float)$c->total;
            }

            // Convertir proveedores a lista plana para cada mes y ordenar por mes
            $result = [];
            ksort($grouped);
            foreach ($grouped as $mesData) {
                $mesData['proveedores'] = array_values($mesData['proveedores']);
                $result[] = $mesData;
            }

            return response()->json($result);
        }

        return response()->json([]);
    }
}
