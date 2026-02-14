<?php

namespace App;

use App\SiatSucursal;
use App\SiatPuntoVenta;
use App\SiatActividadEconomica;
use Illuminate\Database\Eloquent\Model;

class SiatProductoServicio extends Model
{
    protected $table = 'siat_producto_servicios';

    protected $fillable =[

        "codigo_actividad",
        "codigo_producto", 
        "descripcion_producto", 
        "usuario_alta",
        "usuario_modificacion"
    ];

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
