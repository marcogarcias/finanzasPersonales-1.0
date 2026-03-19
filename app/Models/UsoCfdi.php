<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsoCfdi extends Model
{
    use HasFactory;

    protected $table = 'cat_uso_cfdi';

    protected $fillable = [
        'clave',
        'descripcion',
        'persona_fisica',
        'persona_moral',
        'regimen_fiscal_receptor',
    ];

    /**
     * Relación con los proveedores.
     */
    public function proveedores()
    {
        return $this->hasMany(Proveedor::class, 'uso_cfdi_id');
    }
}
