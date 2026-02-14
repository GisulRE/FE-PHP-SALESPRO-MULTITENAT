<?php

namespace App;

use App\SiatSucursal;
use App\SiatPuntoVenta;
use App\SiatLeyendaFactura;
use Illuminate\Database\Eloquent\Model;

class SiatActividadEconomica extends Model
{
    protected $table = 'siat_actividades_economicas';

    protected $fillable =[

        "codigo_caeb", 
        "descripcion", 
        "tipo_actividad",
        "usuario_alta",
        "usuario_modificacion"
    ];

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
