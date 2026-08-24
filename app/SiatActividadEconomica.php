<?php

namespace App;

use App\SiatSucursal;
use App\SiatPuntoVenta;
use App\SiatLeyendaFactura;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class SiatActividadEconomica extends Model
{
    protected $table = 'siat_actividades_economicas';

    protected $fillable =[

        "codigo_caeb", 
        "descripcion", 
        "tipo_actividad",
        "usuario_alta",
        "usuario_modificacion",
        "company_id"
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

    public function legends()
    {
        return $this->hasMany(SiatLeyendaFactura::class);
    }

    public function getPuntoVenta()
    {
        return $this->belongsTo(SiatPuntoVenta::class, 'codigo_punto_venta', 'codigo_punto_venta' );
    }
    public function getSucursal()
    {
        return $this->belongsTo(SiatSucursal::class, 'sucursal', 'sucursal' );
    }
}
