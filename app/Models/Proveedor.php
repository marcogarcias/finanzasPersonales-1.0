<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proveedor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'proveedores';

    protected $fillable = [
        'user_id',
        'rfc_receptor',
        'rfc',
        'nombre',
        'tipo_de_uso',
        'efecto_fiscal',
        'momento_fiscal',
        'categoria',
        'concepto',
        'uso_cfdi_id',
    ];

    /**
     * Relación con el Uso CFDI.
     */
    public function usoCfdi()
    {
        return $this->belongsTo(UsoCfdi::class, 'uso_cfdi_id');
    }
}
