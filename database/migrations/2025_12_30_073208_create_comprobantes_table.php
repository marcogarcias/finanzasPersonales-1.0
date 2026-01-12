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
            $table->string('uuid', 36)->unique();
            $table->string('estado_sat', 15)->comment('aceptado, rechazado, cancelado');
            $table->string('estado_validacion', 15)->nullable()->comment('validado, no_validado');
            $table->string('no_certificado', 20);
            $table->string('no_certificado_sat', 20);
            $table->decimal('version', 5, 2);
            $table->string('tipo_comprobante', 4)->comment('I, E, P, T, N');
            $table->string('tipo', 15)->nullable()->comment('Factura, Nota de crédito, Nota de débito');
            $table->dateTime('fecha')->comment('Fecha de emisión');
            $table->dateTime('fecha_timbrado')->comment('Fecha de timbrado');
            $table->integer('anio');
            $table->tinyInteger('mes');
            $table->tinyInteger('dia');
            $table->string('estado_pago', 20)->nullable();
            $table->dateTime('fecha_pago')->nullable();
            $table->string('serie')->nullable();
            $table->string('folio')->nullable();
            $table->string('tipo_relacion')->nullable();
            $table->string('uuid_relacion')->nullable();
            $table->string('rfc_emisor', 15);
            $table->string('nombre_emisor', 100)->nullable();
            $table->integer('regimen_fiscal');
            $table->integer('lugar_expedicion');
            $table->string('rfc_receptor', 15);
            $table->string('nombre_receptor', 100)->nullable();
            $table->string('residencia_fiscal', 100)->nullable();
            $table->string('direccion_emisor')->nullable();
            $table->string('localidad_emisor')->nullable();
            $table->string('direccion_receptor')->nullable();
            $table->string('localidad_receptor')->nullable();
            $table->string('regimen_fiscal_receptor')->nullable();
            $table->string('domicilio_fiscal_receptor', 10)->nullable();
            $table->string('numRegIdTrib', 100)->nullable();
            $table->string('uso_cfdi', 100)->nullable();
            $table->decimal('subtotal', 18, 4);
            $table->decimal('descuento', 18, 4)->nullable()->default(0);
            $table->decimal('total_ieps', 18, 4)->nullable()->default(0);
            $table->decimal('iva_16', 18, 4)->nullable();
            $table->decimal('retenido_iva', 18, 4)->nullable()->default(0);
            $table->decimal('retenido_isr', 18, 4)->nullable()->default(0);
            $table->decimal('ish', 18, 4)->nullable()->default(0);
            $table->decimal('total', 18, 4);
            $table->decimal('total_original', 18, 4)->nullable()->default(0);
            $table->decimal('total_traslados', 18, 4)->nullable()->default(0);
            $table->decimal('total_retenidos', 18, 4)->nullable()->default(0);
            $table->decimal('total_local_traslado', 18, 4)->nullable()->default(0);
            $table->decimal('total_local_retenido', 18, 4)->nullable()->default(0);
            $table->string('complemento', 100)->nullable();
            $table->string('moneda', 5)->default('MXN');
            $table->decimal('tipo_cambio', 18, 4)->default(1);
            $table->string('forma_pago', 100)->nullable();
            $table->string('metodo_pago', 100)->nullable();
            $table->string('num_cta_pago')->nullable();
            $table->string('condicion_pago', 100)->nullable();
            $table->text('conceptos')->nullable();
            $table->string('combustible', 5)->nullable()->default('no');
            $table->decimal('ieps_3', 18, 4)->nullable()->default(0);
            $table->decimal('ieps_6', 18, 4)->nullable()->default(0);
            $table->decimal('ieps_7', 18, 4)->nullable()->default(0);
            $table->decimal('ieps_8', 18, 4)->nullable()->default(0);
            $table->decimal('ieps_9', 18, 4)->nullable()->default(0);
            $table->decimal('ieps_26_5', 18, 4)->nullable()->default(0);
            $table->decimal('ieps_30', 18, 4)->nullable()->default(0);
            $table->decimal('ieps_53', 18, 4)->nullable()->default(0);
            $table->decimal('ieps_160', 18, 4)->nullable()->default(0);
            $table->string('archivo_xml', 100);
            $table->decimal('iva_8', 18, 4)->nullable()->default(0);
            $table->decimal('ieps_30_4', 18, 4)->nullable()->default(0);
            $table->decimal('iva_rep_6', 18, 4)->nullable()->default(0);
            $table->text('xml_path');
        
            $table->timestamps();
            $table->softDeletes();
            
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
