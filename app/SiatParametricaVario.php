<?php

namespace App;

use App\SiatSucursal;
use App\SiatPuntoVenta;
use App\SiatActividadEconomica;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SiatParametricaVario extends Model
{
    protected $table = 'siat_parametricas_varios';

    protected $fillable =[
        "company_id",
        "id_empresa",
        "estado",
        "tipo_clasificador",

        "codigo_clasificador", 
        "descripcion", 
        "datos",

        "usuario_alta",
        "usuario_modificacion",

        "sucursal",
        "codigo_punto_venta"
    ];

    protected static $sucursalesCache = null;
    protected static $puntosVentaCache = null;

    protected static function boot()
    {
        parent::boot();

        $table = (new static)->getTable();
        static::addGlobalScope('company', function (Builder $builder) use ($table) {
            if (auth()->check() && auth()->user()->company_id) {
                $builder->where(function ($q) use ($table) {
                    $q->where($table . '.company_id', auth()->user()->company_id)
                      ->orWhereNull($table . '.company_id');
                });
            }
        });

        static::creating(function ($model) {
            if (auth()->check() && empty($model->company_id)) {
                $model->company_id = auth()->user()->company_id;
            }
        });
    }

    public function getPuntoVenta()
    {
        if (self::$puntosVentaCache === null) {
            self::$puntosVentaCache = SiatPuntoVenta::pluck('nombre_punto_venta', 'codigo_punto_venta')->toArray();
        }
        return self::$puntosVentaCache[$this->codigo_punto_venta] ?? ($this->codigo_punto_venta == 0 ? 'Central' : 'Punto ' . $this->codigo_punto_venta);
    }

    public function getSucursal()
    {
        if (self::$sucursalesCache === null) {
            self::$sucursalesCache = SiatSucursal::pluck('nombre', 'sucursal')->toArray();
        }
        return self::$sucursalesCache[$this->sucursal] ?? ($this->sucursal == 0 ? 'Casa Matriz' : 'Sucursal ' . $this->sucursal);
    }
}
