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
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('rfc_receptor', 15)->nullable(); // El RFC del cliente que recibe la factura
            $table->string('rfc', 15); // El RFC del proveedor (emisor)
            $table->string('nombre');
            $table->string('tipo_de_uso')->nullable();
            $table->string('efecto_fiscal')->nullable();
            $table->string('momento_fiscal')->nullable();
            $table->string('categoria')->nullable();
            $table->text('concepto')->nullable();
            
            // Relación con el catálogo de Uso CFDI
            $table->foreignId('uso_cfdi_id')->nullable()->constrained('cat_uso_cfdi')->onDelete('set null');

            $table->unique(['user_id', 'rfc_receptor', 'rfc']);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
