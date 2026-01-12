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
     * @return array
     */
    public static function parse(string $xmlContent, int $userId, string $clase, string $xmlPath): array
    {
        // Convertir XML a Array usando la librería de phpcfdi
        $data = JsonConverter::convertToArray($xmlContent);

        // Referencias rápidas a nodos principales
        $comprobante = $data;
        $emisor = $data['Emisor'] ?? [];
        $receptor = $data['Receptor'] ?? [];
        $timbre = $data['Complemento']['TimbreFiscalDigital'] ?? [];
        $conceptosNodes = $data['Conceptos'] ?? [];

        // Extraer y unir descripciones de conceptos con |
        $descripciones = [];
        if (isset($conceptosNodes['Concepto'])) {
            $items = isset($conceptosNodes['Concepto'][0]) ? $conceptosNodes['Concepto'] : [$conceptosNodes['Concepto']];
            foreach ($items as $item) {
                $descripciones[] = $item['Descripcion'] ?? '';
            }
        }
        $conceptosString = implode(' | ', $descripciones);

        // Manejo de Impuestos Trasladados (IVA e IEPS)
        $totalTrasladados = $comprobante['Impuestos']['TotalImpuestosTrasladados'] ?? 0;
        $totalRetenidos = $comprobante['Impuestos']['TotalImpuestosRetenidos'] ?? 0;
        
        $impuestos = [
            'iva_16' => 0,
            'iva_8' => 0,
            'ieps_3' => 0, 'ieps_6' => 0, 'ieps_7' => 0, 'ieps_8' => 0, 'ieps_9' => 0,
            'ieps_26_5' => 0, 'ieps_30' => 0, 'ieps_30_4' => 0, 'ieps_53' => 0, 'ieps_160' => 0,
        ];

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

        // Manejo de Retenciones
        $retenidoIva = 0;
        $retenidoIsr = 0;
        if (isset($data['Impuestos']['Retenciones']['Retencion'])) {
            $retenciones = isset($data['Impuestos']['Retenciones']['Retencion'][0]) 
                ? $data['Impuestos']['Retenciones']['Retencion'] 
                : [$data['Impuestos']['Retenciones']['Retencion']];
            
            foreach ($retenciones as $r) {
                if (($r['Impuesto'] ?? '') == '001') $retenidoIsr += floatval($r['Importe'] ?? 0);
                if (($r['Impuesto'] ?? '') == '002') $retenidoIva += floatval($r['Importe'] ?? 0);
            }
        }

        $fechaEmision = isset($comprobante['Fecha']) ? Carbon::parse($comprobante['Fecha']) : null;

        return [
            'user_id' => $userId,
            'clase_comprobante' => $clase,
            'uuid' => $timbre['UUID'] ?? null,
            'estado_sat' => 'vigente', // Por defecto al descargar/importar
            'no_certificado' => $comprobante['NoCertificado'] ?? '',
            'no_certificado_sat' => $timbre['NoCertificadoSAT'] ?? '',
            'version' => $comprobante['Version'] ?? '4.0',
            'tipo_comprobante' => $comprobante['TipoDeComprobante'] ?? 'I',
            'fecha' => $fechaEmision,
            'fecha_timbrado' => isset($timbre['FechaTimbrado']) ? Carbon::parse($timbre['FechaTimbrado']) : null,
            'anio' => $fechaEmision ? $fechaEmision->year : null,
            'mes' => $fechaEmision ? $fechaEmision->month : null,
            'dia' => $fechaEmision ? $fechaEmision->day : null,
            'serie' => $comprobante['Serie'] ?? null,
            'folio' => $comprobante['Folio'] ?? null,
            'rfc_emisor' => $emisor['Rfc'] ?? '',
            'nombre_emisor' => $emisor['Nombre'] ?? null,
            'regimen_fiscal' => $emisor['RegimenFiscal'] ?? 0,
            'lugar_expedicion' => $comprobante['LugarExpedicion'] ?? 0,
            'rfc_receptor' => $receptor['Rfc'] ?? '',
            'nombre_receptor' => $receptor['Nombre'] ?? null,
            'uso_cfdi' => $receptor['UsoCFDI'] ?? null,
            'regimen_fiscal_receptor' => $receptor['RegimenFiscalReceptor'] ?? null,
            'domicilio_fiscal_receptor' => $receptor['DomicilioFiscalReceptor'] ?? null,
            'subtotal' => $comprobante['SubTotal'] ?? 0,
            'descuento' => $comprobante['Descuento'] ?? 0,
            'iva_16' => $impuestos['iva_16'],
            'iva_8' => $impuestos['iva_8'],
            'ieps_3' => $impuestos['ieps_3'],
            'ieps_6' => $impuestos['ieps_6'],
            'ieps_7' => $impuestos['ieps_7'],
            'ieps_8' => $impuestos['ieps_8'],
            'ieps_9' => $impuestos['ieps_9'],
            'ieps_26_5' => $impuestos['ieps_26_5'],
            'ieps_30' => $impuestos['ieps_30'],
            'ieps_30_4' => $impuestos['ieps_30_4'],
            'ieps_53' => $impuestos['ieps_53'],
            'ieps_160' => $impuestos['ieps_160'],
            'retenido_iva' => $retenidoIva,
            'retenido_isr' => $retenidoIsr,
            'total_traslados' => $totalTrasladados,
            'total_retenidos' => $totalRetenidos,
            'total' => $comprobante['Total'] ?? 0,
            'moneda' => $comprobante['Moneda'] ?? 'MXN',
            'tipo_cambio' => $comprobante['TipoCambio'] ?? 1,
            'forma_pago' => $comprobante['FormaPago'] ?? null,
            'metodo_pago' => $comprobante['MetodoPago'] ?? null,
            'conceptos' => $conceptosString,
            'archivo_xml' => basename($xmlPath),
            'xml_path' => $xmlPath,
        ];
    }
}
