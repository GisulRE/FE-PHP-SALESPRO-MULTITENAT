<?php

namespace App;

use App\SiatSucursal;
use App\SiatPuntoVenta;
use App\SiatActividadEconomica;
use Illuminate\Database\Eloquent\Model;

class SiatParametricaVario extends Model
{
    protected $table = 'siat_parametricas_varios';

    protected $fillable =[

        "tipo_clasificador",

        "codigo_clasificador", 
        "descripcion", 

        "usuario_alta",
        "usuario_modificacion",

        "sucursal",
        "codigo_punto_venta",
        "company_id"
    ];

    protected static function boot()
    {
        parent::boot();

        if (\Illuminate\Support\Facades\Schema::hasColumn((new static)->getTable(), 'company_id')) {
            static::addGlobalScope('company', function (\Illuminate\Database\Eloquent\Builder $builder) {
                if (auth()->check() && auth()->user()->company_id) {
                    $builder->where(function ($q) {
                        $q->where((new static)->getTable() . '.company_id', auth()->user()->company_id)
                          ->orWhereNull((new static)->getTable() . '.company_id');
                    });
                }
            });

            static::creating(function ($model) {
                if (auth()->check() && empty($model->company_id)) {
                    $model->company_id = auth()->user()->company_id;
                }
            });
        }
    }

    public function getPuntoVenta()
    {
        return $punto = SiatPuntoVenta::where('codigo_punto_venta', $this->codigo_punto_venta)->pluck('nombre_punto_venta')->first();
    }
    public function getSucursal()
    {
        return $punto = SiatSucursal::where('sucursal', $this->sucursal)->pluck('nombre')->first();
    }
}
