<?php

namespace App;

use App\SiatParametricaVario;
use App\SiatActividadEconomica;
use Illuminate\Database\Eloquent\Model;

class SiatDocumentoSector extends Model
{
    protected $table = 'siat_documento_sector';

    protected $fillable =[

        "codigo_actividad",
        "codigo_documento_sector", 
        "tipo_documento_sector", 
        "usuario_alta",
        "usuario_modificacion"
    ];

    public function activity()
    {
        return $this->belongsTo(SiatActividadEconomica::class, 'codigo_actividad', 'codigo_caeb');
    }


    public function getDescripcionDocumento()
    {
        return SiatParametricaVario::where('tipo_clasificador', 'tipoDocumentoSector')->
                        where('codigo_clasificador', $this->codigo_documento_sector)->pluck('descripcion')->first();
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
