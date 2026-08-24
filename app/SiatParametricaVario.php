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
        "codigo_punto_venta"
    ];

    public function getPuntoVenta()
    {
        return $punto = SiatPuntoVenta::where('codigo_punto_venta', $this->codigo_punto_venta)->pluck('nombre_punto_venta')->first();
    }
    public function getSucursal()
    {
        return $punto = SiatSucursal::where('sucursal', $this->sucursal)->pluck('nombre')->first();
    }
}
