<?php

namespace App;

use App\SiatActividadEconomica;
use Illuminate\Database\Eloquent\Model;

class SiatLeyendaFactura extends Model
{
    protected $table = 'siat_leyendas_facturas';

    protected $fillable =[

        "codigo_actividad",
        "descripcion_leyenda", 
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
