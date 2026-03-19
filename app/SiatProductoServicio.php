<?php

namespace App;

use App\SiatSucursal;
use App\SiatPuntoVenta;
use App\SiatActividadEconomica;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class SiatProductoServicio extends Model
{
    protected $table = 'siat_producto_servicios';

    protected $fillable =[

        "codigo_actividad",
        "codigo_producto", 
        "descripcion_producto", 
        "usuario_alta",
        "usuario_modificacion",
        "company_id"
    ];

    protected static function boot()
    {
        parent::boot();

        $table = (new static)->getTable();
        if (Schema::hasColumn($table, 'company_id')) {
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

    public function activity()
    {
        return $this->belongsTo(SiatActividadEconomica::class, 'codigo_actividad', 'codigo_caeb');
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
