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
        Schema::create('rfcs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('rfc', 15);
            $table->string('razon_social')->nullable();
            $table->string('regimen_fiscal')->nullable();
            $table->string('password')->nullable(); // Solo para CIEC
            
            // Un usuario no debería tener el mismo RFC duplicado en su lista
            $table->unique(['user_id', 'rfc']);
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfcs');
    }
};
