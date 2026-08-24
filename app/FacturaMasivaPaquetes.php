<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class FacturaMasivaPaquetes extends Model
{
    protected $table = 'factura_masiva_paquetes';

    protected $fillable =[
        "factura_masiva_id", 
        "cantidad_ventas", 

        "fecha_de_envio", 
        "glosa_nro_factura_inicio_a_fin", 
        "arreglo_ventas", 

        "codigo_recepcion", 
        "respuesta_servicio", 
        "log_errores",  

        "estado",
        "company_id", 
    ];

    protected static function boot()
    {
        parent::boot();

        $table = (new static)->getTable();
        if (true) {
            static::addGlobalScope('company', function (Builder $builder) use ($table) {
                if (auth()->check()) {
                    $builder->where($table . '.company_id', auth()->user()->company_id);
                }
            });

            static::creating(function ($model) {
                if (auth()->check() && empty($model->company_id)) {
                    $model->company_id = auth()->user()->company_id;
                }
            });
        }
    }
}
