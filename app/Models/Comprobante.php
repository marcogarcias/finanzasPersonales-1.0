<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comprobante extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'comprobantes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'clase_comprobante',
        'uuid',
        'estado_sat',
        'estado_validacion',
        'no_certificado',
        'no_certificado_sat',
        'version',
        'tipo_comprobante',
        'tipo',
        'fecha',
        'fecha_timbrado',
        'anio',
        'mes',
        'dia',
        'estado_pago',
        'fecha_pago',
        'serie',
        'folio',
        'tipo_relacion',
        'uuid_relacion',
        'rfc_emisor',
        'nombre_emisor',
        'regimen_fiscal',
        'lugar_expedicion',
        'rfc_receptor',
        'nombre_receptor',
        'residencia_fiscal',
        'direccion_emisor',
        'localidad_emisor',
        'direccion_receptor',
        'localidad_receptor',
        'regimen_fiscal_receptor',
        'domicilio_fiscal_receptor',
        'numRegIdTrib',
        'uso_cfdi',
        'subtotal',
        'descuento',
        'total_ieps',
        'iva_16',
        'retenido_iva',
        'retenido_isr',
        'ish',
        'total',
        'total_original',
        'total_traslados',
        'total_retenidos',
        'total_local_traslado',
        'total_local_retenido',
        'complemento',
        'moneda',
        'tipo_cambio',
        'forma_pago',
        'metodo_pago',
        'num_cta_pago',
        'condicion_pago',
        'conceptos',
        'combustible',
        'ieps_3',
        'ieps_6',
        'ieps_7',
        'ieps_8',
        'ieps_9',
        'ieps_26_5',
        'ieps_30',
        'ieps_53',
        'ieps_160',
        'archivo_xml',
        'iva_8',
        'ieps_30_4',
        'iva_rep_6',
        'xml_path',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha' => 'datetime',
        'fecha_timbrado' => 'datetime',
        'fecha_pago' => 'datetime',
        'version' => 'decimal:2',
        'subtotal' => 'decimal:4',
        'descuento' => 'decimal:4',
        'total_ieps' => 'decimal:4',
        'iva_16' => 'decimal:4',
        'retenido_iva' => 'decimal:4',
        'retenido_isr' => 'decimal:4',
        'ish' => 'decimal:4',
        'total' => 'decimal:4',
        'total_original' => 'decimal:4',
        'total_traslados' => 'decimal:4',
        'total_retenidos' => 'decimal:4',
        'total_local_traslado' => 'decimal:4',
        'total_local_retenido' => 'decimal:4',
        'tipo_cambio' => 'decimal:4',
        'ieps_3' => 'decimal:4',
        'ieps_6' => 'decimal:4',
        'ieps_7' => 'decimal:4',
        'ieps_8' => 'decimal:4',
        'ieps_9' => 'decimal:4',
        'ieps_26_5' => 'decimal:4',
        'ieps_30' => 'decimal:4',
        'ieps_53' => 'decimal:4',
        'ieps_160' => 'decimal:4',
        'iva_8' => 'decimal:4',
        'ieps_30_4' => 'decimal:4',
        'iva_rep_6' => 'decimal:4',
    ];

    /**
     * Get the user that owns the comprobante.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
