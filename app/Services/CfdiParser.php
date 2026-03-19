<?php

namespace App\Services;

use PhpCfdi\CfdiToJson\JsonConverter;
use Illuminate\Support\Carbon;

class CfdiParser
{
    /**
     * Parsea un XML de CFDI y devuelve un array mapeado para el modelo Comprobante.
     *
     * @param string $xmlContent Contenido bruto del XML.
     * @param int $userId ID del usuario al que pertenece.
     * @param string $clase emitido|recibido
     * @param string $xmlPath Ruta física del archivo.
     * @param string|null $satStatus Estatus de metadata (vigente/cancelado).
     * @param string|null $externalUuid UUID de respaldo si no se encuentra en el XML.
     * @return array
     */
    public static function parse(string $xmlContent, int $userId, string $clase, string $xmlPath, string $satStatus = null, string $externalUuid = null): array
    {
        // Convertir XML a Array usando la librería de phpcfdi
        $data = JsonConverter::convertToArray($xmlContent);

        // Referencias rápidas a nodos principales
        $comprobante = $data;
        $emisor = $data['Emisor'] ?? [];
        $receptor = $data['Receptor'] ?? [];
        
        // Manejo del Timbre Fiscal Digital (UUID)
        $complemento = $data['Complemento'] ?? [];
        $timbre = $complemento['TimbreFiscalDigital'] ?? [];
        
        // Si no está directo, buscar en una lista de complementos
        if (empty($timbre) && isset($complemento[0])) {
            foreach ($complemento as $comp) {
                if (isset($comp['TimbreFiscalDigital'])) {
                    $timbre = $comp['TimbreFiscalDigital'];
                    break;
                }
            }
        }

        // Manejo de CFDI Relacionados
        $relacionados = $data['CfdiRelacionados'] ?? [];
        $tipoRelacion = $relacionados['TipoRelacion'] ?? null;
        $uuidRelacion = null;
        if (isset($relacionados['CfdiRelacionado'])) {
            $rel = $relacionados['CfdiRelacionado'];
            $uuidRelacion = isset($rel[0]) ? $rel[0]['UUID'] : ($rel['UUID'] ?? null);
        }

        // Extracción de conceptos
        $conceptosNodes = $data['Conceptos'] ?? [];
        $descripciones = [];
        if (isset($conceptosNodes['Concepto'])) {
            $items = isset($conceptosNodes['Concepto'][0]) ? $conceptosNodes['Concepto'] : [$conceptosNodes['Concepto']];
            foreach ($items as $item) {
                $descripciones[] = $item['Descripcion'] ?? '';
            }
        }
        $conceptosString = implode(' | ', $descripciones);

        // Totales de impuestos
        $totalTrasladados = $comprobante['Impuestos']['TotalImpuestosTrasladados'] ?? ($comprobante['TotalImpuestosTrasladados'] ?? 0);
        $totalRetenidos = $comprobante['Impuestos']['TotalImpuestosRetenidos'] ?? ($comprobante['TotalImpuestosRetenidos'] ?? 0);
        
        $impuestos = [
            'iva_16' => 0, 'iva_8' => 0,
            'ieps_3' => 0, 'ieps_6' => 0, 'ieps_7' => 0, 'ieps_8' => 0, 'ieps_9' => 0,
            'ieps_26_5' => 0, 'ieps_30' => 0, 'ieps_30_4' => 0, 'ieps_53' => 0, 'ieps_160' => 0,
        ];

        // Procesar Traslados detallados
        if (isset($data['Impuestos']['Traslados']['Traslado'])) {
            $traslados = isset($data['Impuestos']['Traslados']['Traslado'][0]) 
                ? $data['Impuestos']['Traslados']['Traslado'] 
                : [$data['Impuestos']['Traslados']['Traslado']];
            
            foreach ($traslados as $t) {
                $impuesto = $t['Impuesto'] ?? '';
                $tasa = floatval($t['TasaOCuota'] ?? 0);
                $importe = floatval($t['Importe'] ?? 0);

                if ($impuesto == '002') { // IVA
                    if ($tasa == 0.16) $impuestos['iva_16'] += $importe;
                    if ($tasa == 0.08) $impuestos['iva_8'] += $importe;
                } elseif ($impuesto == '003') { // IEPS
                    if ($tasa == 0.03) $impuestos['ieps_3'] += $importe;
                    if ($tasa == 0.06) $impuestos['ieps_6'] += $importe;
                    if ($tasa == 0.07) $impuestos['ieps_7'] += $importe;
                    if ($tasa == 0.08) $impuestos['ieps_8'] += $importe;
                    if ($tasa == 0.09) $impuestos['ieps_9'] += $importe;
                    if ($tasa == 0.265) $impuestos['ieps_26_5'] += $importe;
                    if ($tasa == 0.30) $impuestos['ieps_30'] += $importe;
                    if ($tasa == 0.304) $impuestos['ieps_30_4'] += $importe;
                    if ($tasa == 0.53) $impuestos['ieps_53'] += $importe;
                    if ($tasa == 1.60) $impuestos['ieps_160'] += $importe;
                }
            }
        }

        // Procesar Retenciones detalladas (IVA, ISR)
        $retenidoIva = 0;
        $retenidoIsr = 0;
        $ivaRet6 = 0;
        if (isset($data['Impuestos']['Retenciones']['Retencion'])) {
            $retenciones = isset($data['Impuestos']['Retenciones']['Retencion'][0]) 
                ? $data['Impuestos']['Retenciones']['Retencion'] 
                : [$data['Impuestos']['Retenciones']['Retencion']];
            
            foreach ($retenciones as $r) {
                $impuesto = $r['Impuesto'] ?? '';
                $importe = floatval($r['Importe'] ?? 0);
                $tasa = floatval($r['TasaOCuota'] ?? 0);

                if ($impuesto == '001') $retenidoIsr += $importe;
                if ($impuesto == '002') {
                    $retenidoIva += $importe;
                    if ($tasa == 0.06) $ivaRet6 += $importe;
                }
            }
        }

        // Impuestos Locales (ISH, etc.)
        $totalLocalTraslado = 0;
        $totalLocalRetenido = 0;
        $ish = 0;
        if (isset($complemento['ImpuestosLocales'])) {
            $implocal = $complemento['ImpuestosLocales'];
            $totalLocalTraslado = floatval($implocal['TotaldeTraslados'] ?? 0);
            $totalLocalRetenido = floatval($implocal['TotaldeRetenciones'] ?? 0);
            
            if (isset($implocal['TrasladosLocales'])) {
                $trasladosLocales = isset($implocal['TrasladosLocales'][0]) ? $implocal['TrasladosLocales'] : [$implocal['TrasladosLocales']];
                foreach ($trasladosLocales as $tl) {
                    if (stripos($tl['ImpLocTrasladado'] ?? '', 'ISH') !== false) {
                        $ish += floatval($tl['Importe'] ?? 0);
                    }
                }
            }
        }

        $fechaEmision = isset($comprobante['Fecha']) ? Carbon::parse($comprobante['Fecha']) : null;
        $uuidRaw = $timbre['UUID'] ?? ($externalUuid ?? null);

        return [
            'user_id' => $userId,
            'clase_comprobante' => $clase,
            'uuid' => $uuidRaw ? strtolower(trim($uuidRaw)) : null,
            'estado_sat' => $satStatus ? strtolower($satStatus) : 'vigente',
            'version' => $comprobante['Version'] ?? ($comprobante['version'] ?? '3.3'),
            'tipo_comprobante' => $comprobante['TipoDeComprobante'] ?? ($comprobante['tipoDeComprobante'] ?? 'I'),
            'fecha' => $fechaEmision,
            'fecha_timbrado' => isset($timbre['FechaTimbrado']) ? Carbon::parse($timbre['FechaTimbrado']) : $fechaEmision,
            'serie' => $comprobante['Serie'] ?? ($comprobante['serie'] ?? null),
            'folio' => $comprobante['Folio'] ?? ($comprobante['folio'] ?? null),
            'uuid_relacion' => $uuidRelacion,
            'tipo_relacion' => $tipoRelacion,
            'rfc_emisor' => $emisor['Rfc'] ?? ($emisor['rfc'] ?? ''),
            'nombre_emisor' => $emisor['Nombre'] ?? ($emisor['nombre'] ?? null),
            'regimen_fiscal' => $emisor['RegimenFiscal'] ?? ($emisor['regimenFiscal'] ?? 0),
            'lugar_expedicion' => $comprobante['LugarExpedicion'] ?? ($comprobante['lugarExpedicion'] ?? 0),
            'rfc_receptor' => $receptor['Rfc'] ?? ($receptor['rfc'] ?? ''),
            'nombre_receptor' => $receptor['Nombre'] ?? ($receptor['nombre'] ?? null),
            'residencia_fiscal' => $comprobante['ResidenciaFiscal'] ?? ($comprobante['residenciaFiscal'] ?? null),
            'uso_cfdi' => $receptor['UsoCFDI'] ?? ($receptor['usoCFDI'] ?? null),
            'subtotal' => $comprobante['SubTotal'] ?? ($comprobante['subTotal'] ?? 0),
            'descuento' => $comprobante['Descuento'] ?? ($comprobante['descuento'] ?? 0),
            'total_ieps' => array_sum(array_intersect_key($impuestos, array_flip(['ieps_3', 'ieps_6', 'ieps_7', 'ieps_8', 'ieps_9', 'ieps_26_5', 'ieps_30', 'ieps_53', 'ieps_160']))),
            'iva_16' => $impuestos['iva_16'],
            'retenido_iva' => $retenidoIva,
            'retenido_isr' => $retenidoIsr,
            'ish' => $ish,
            'total' => $comprobante['Total'] ?? ($comprobante['total'] ?? 0),
            'total_traslados' => $totalTrasladados,
            'total_retenidos' => $totalRetenidos,
            'total_local_traslado' => $totalLocalTraslado,
            'total_local_retenido' => $totalLocalRetenido,
            'complemento' => null,
            'moneda' => $comprobante['Moneda'] ?? ($comprobante['moneda'] ?? 'MXN'),
            'tipo_cambio' => $comprobante['TipoCambio'] ?? ($comprobante['tipoCambio'] ?? 1),
            'forma_pago' => $comprobante['FormaPago'] ?? ($comprobante['formaPago'] ?? null),
            'metodo_pago' => $comprobante['MetodoPago'] ?? ($comprobante['metodoPago'] ?? null),
            'conceptos' => $conceptosString,
            'combustible' => 'no',
            'ieps_3' => $impuestos['ieps_3'],
            'ieps_6' => $impuestos['ieps_6'],
            'ieps_7' => $impuestos['ieps_7'],
            'ieps_8' => $impuestos['ieps_8'],
            'ieps_9' => $impuestos['ieps_9'],
            'ieps_26_5' => $impuestos['ieps_26_5'],
            'ieps_30' => $impuestos['ieps_30'],
            'ieps_53' => $impuestos['ieps_53'],
            'ieps_160' => $impuestos['ieps_160'],
            'archivo_xml' => basename($xmlPath),
            'direccion_emisor' => $comprobante['LugarExpedicion'] ?? ($comprobante['lugarExpedicion'] ?? null),
            'direccion_receptor' => $receptor['DomicilioFiscalReceptor'] ?? ($receptor['domicilioFiscalReceptor'] ?? null),
            'regimen_fiscal_receptor' => $receptor['RegimenFiscalReceptor'] ?? ($receptor['regimenFiscalReceptor'] ?? null),
            'iva_8' => $impuestos['iva_8'],
            'ieps_30_4' => $impuestos['ieps_30_4'],
            'iva_ret_6' => $ivaRet6,
            'xml_path' => $xmlPath,
        ];
    }
}
