<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActividadEconomica extends Model
{
    use HasFactory;

    protected $table = 'actividades_economicas';

    protected $fillable = [
        'actividad',
        'categoria'
    ];

    public function proveedores()
    {
        return $this->hasMany(Proveedor::class, 'actividad_economica_id');
    }
}
