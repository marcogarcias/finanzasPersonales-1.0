<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comprobantes', function (Blueprint $table) {
            $table->id();
            // Relación con el usuario local
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Dirección del comprobante (Emitido o Recibido)
            $table->string('clase_comprobante')->comment('emitido, recibido');

            // Atributos base del comprobante
            $table->string('estado_sat', 15)->comment('vigente, cancelado'); // vigente/cancelado  viene de metadata
            $table->decimal('version', 5, 2); // cfdi:Comprobante>Version="4.0"; puede ser 3.2 o 4.0
            $table->string('tipo_comprobante', 4)->comment('I, E, P, T, N'); // cfdi:Comprobante>TipoDeComprobante="I"; Ingreso=factura, Egreso=nota de crédito, Pago=confirmación de pago
            $table->dateTime('fecha')->comment('Fecha de emisión'); // cfdi:Comprobante>Fecha="2024-01-03T12:34:49"
            $table->dateTime('fecha_timbrado')->comment('Fecha de timbrado'); // cfdi:Complemento>tfd:TimbreFiscalDigital>FechaTimbrado="2024-01-03T12:34:49"
            $table->string('serie')->nullable(); // cfdi:Comprobante>Serie="FAC"
            $table->string('folio')->nullable(); // cfdi:Comprobante>Folio="123456";
            $table->string('uuid', 36)->unique(); // cfdi:Complemento>tfd:TimbreFiscalDigital>UUID="12345678-1234-1234-1234-123456789012"
            $table->string('uuid_relacion')->nullable(); // cfdi:Comprobante>CfdiRelacionados>CfdiRelacionado
            $table->string('tipo_relacion')->nullable(); // cfdi:Comprobante>CfdiRelacionados>TipoRelacion="04"
            $table->string('rfc_emisor', 15); // cfdi:Comprobant>Emisor Rfc="CAA880229QR0"
            $table->string('nombre_emisor', 100)->nullable(); // cfdi:Comprobant>Emisor Nombre="Comercial S.A. de C.V."
            $table->integer('lugar_expedicion'); // cfdi:Comprobante>LugarExpedicion="03300"
            $table->string('rfc_receptor', 15); // cfdi:Comprobante>Receptor Rfc="COJX85XXXXXX"
            $table->string('nombre_receptor', 100)->nullable(); // cfdi:Comprobante>Receptor Nombre="JERO CROS NICOL"
            $table->string('residencia_fiscal', 100)->nullable(); // solo cuando es un extranjero cfdi:Comprobante>Receptor>ResidenciaFiscal="ESP"
            $table->string('uso_cfdi', 100)->nullable(); // cfdi:Comprobante>Receptor>UsoCfdi="P01"
            $table->decimal('subtotal', 18, 4); // cfdi:Comprobante>SubTotal="12345.67"
            $table->decimal('descuento', 18, 4)->nullable()->default(0); // cfdi:Comprobante>Descuento="3931.2"
            $table->decimal('total_ieps', 18, 4)->nullable()->default(0); // cfdi:Impuestos>Traslados>Traslado Impuesto="003" ... Importe="8068.81"
            $table->decimal('iva_16', 18, 4)->nullable(); // el que tenga el atributo Impuesto="002" en cfdi:Comprobante>Impuestos>Traslados>cfdi:Traslado>Importe="28.53"
            $table->decimal('retenido_iva', 18, 4)->nullable()->default(0); //Impuestos>Retenciones>cfdi:Retencion Impuesto="002" Importe="5379.22"
            $table->decimal('retenido_isr', 18, 4)->nullable()->default(0); //Impuestos>Retenciones>cfdi:Retencion Impuesto="001" Importe="5379.22"
            $table->decimal('ish', 18, 4)->nullable()->default(0); // impuesto sobre hospedaje, solo poner en la base un true si es ish o false si no lo es cfdi:Comprobante>Complemento>ImpuestosLocales>TrasladosLocales>ImpLocTrasladado="ISH"
            $table->decimal('total', 18, 4); // cfdi:Comprobante>Total="12345.67"
            $table->decimal('total_traslados', 18, 4)->nullable()->default(0); //cfdi:Impuestos TotalImpuestosTrasladados="8068.81"
            $table->decimal('total_retenidos', 18, 4)->nullable()->default(0); //cfdi:Impuestos TotalImpuestosRetenidos="5379.22"
            $table->decimal('total_local_traslado', 18, 4)->nullable()->default(0); // cfdi:Comprobante>Complemento>ImpuestosLocales>TotaldeTraslados (atributo)
            $table->decimal('total_local_retenido', 18, 4)->nullable()->default(0); // cfdi:Comprobante>Complemento>ImpuestosLocales>TotaldeRetenciones (atributo)
            $table->string('complemento', 100)->nullable(); // null
            $table->string('moneda', 5)->default('MXN'); // cfdi:Comprobante>Moneda="MXN"
            $table->decimal('tipo_cambio', 18, 4)->default(1); // por deefcto 1. cfdi:Comprobante>TipoCambio="18.7642" (atributo)
            $table->string('forma_pago', 100)->nullable(); // cfdi:Comprobante>FormaPago="01"
            $table->string('metodo_pago', 100)->nullable(); // cfdi:Comprobante>MetodoPago="PUE"
            $table->text('conceptos')->nullable(); // cfdi:Comprobante>Conceptos>Concepto...
            $table->string('combustible', 5)->nullable()->default('no'); // ??????????
            /* INICIO: NODOS DE COMBUSTIBLES */
            $table->decimal('ieps_3', 18, 4)->nullable()->default(0);
            $table->decimal('ieps_6', 18, 4)->nullable()->default(0);
            $table->decimal('ieps_7', 18, 4)->nullable()->default(0);
            $table->decimal('ieps_8', 18, 4)->nullable()->default(0);
            $table->decimal('ieps_9', 18, 4)->nullable()->default(0);
            $table->decimal('ieps_26_5', 18, 4)->nullable()->default(0);
            $table->decimal('ieps_30', 18, 4)->nullable()->default(0);
            $table->decimal('ieps_53', 18, 4)->nullable()->default(0);
            $table->decimal('ieps_160', 18, 4)->nullable()->default(0);
            /* FIN: NODOS DE COMBUSTIBLES */
            $table->string('archivo_xml', 100); // uuid + .xml
            $table->string('direccion_emisor')->nullable(); // cfdi:Comprobante>LugarExpedicion="03300"
            $table->string('regimen_fiscal')->nullable(); // cfdi:Comprobante>Emisor>RegimenFiscal="612"
            $table->string('direccion_receptor')->nullable(); // cfdi:Comprobante>Receptor>DomicilioFiscalReceptor
            $table->string('regimen_fiscal_receptor')->nullable(); // cfdi:Comprobante>Receptor>RegimenFiscalReceptor="601"
            $table->decimal('iva_8', 18, 4)->nullable()->default(0); // Impuestos>Traslados>cfdi:Traslado Impuesto="002" ... TasaOCuota="0.08" Importe="0.50" (cuando tasaOCuota diga 0.08)
            $table->decimal('ieps_30_4', 18, 4)->nullable()->default(0); // Impuestos>Traslados>cfdi:Traslado Impuesto="003" ... TasaOCuota="0.304" Importe="0.50" (cuando tasaOCuota diga 0.304)
            $table->decimal('iva_ret_6', 18, 4)->nullable()->default(0); // ????????
            $table->text('xml_path');
            $table->timestamps();
            $table->softDeletes();

            //$table->string('estado_validacion', 15)->nullable()->comment('validado, no_validado');
            //$table->string('no_certificado', 20);
            //$table->string('no_certificado_sat', 20);
            //$table->string('tipo', 15)->nullable()->comment('Factura, Nota de crédito, Nota de débito');
            //$table->integer('anio');
            //$table->tinyInteger('mes');
            //$table->tinyInteger('dia');
            //$table->integer('regimen_fiscal');
            //$table->string('domicilio_fiscal_receptor', 10)->nullable();
            //$table->string('numRegIdTrib', 100)->nullable();
            //$table->decimal('total_original', 18, 4)->nullable()->default(0);
            //$table->string('num_cta_pago')->nullable();
            //$table->string('condicion_pago', 100)->nullable();
            
            // Índices para velocidad en reportes y filtros
            $table->index('rfc_emisor');
            $table->index('rfc_receptor');
            $table->index('fecha');
            $table->index('clase_comprobante');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comprobantes');
    }
};
