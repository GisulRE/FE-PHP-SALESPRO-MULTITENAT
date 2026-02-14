<?php

namespace App;

use Carbon\Carbon;
use App\GeneralSetting;
use Illuminate\Database\Eloquent\Model;

class FacturaMasiva extends Model
{
    protected $table = 'factura_masiva';

    protected $fillable =[

        "glosa", 
        "fecha_inicio", 
        "fecha_fin", 
        
        "tipo_factura", 
        "estado", 
        "cantidad_paquetes", 

        "cuis", 
        "sucursal", 
        "codigo_punto_venta", 
        "codigo_documento_sector", 
        "created_by"
    ];

    public function getNombreSucursal()
    {
        $sucursal = SiatSucursal::where('sucursal', $this->sucursal)->first();
        return $sucursal->nombre;
    }

    public function getNombrePuntoVenta()
    {
        $punto = SiatPuntoVenta::where('codigo_punto_venta', $this->codigo_punto_venta)->where('sucursal', $this->sucursal)->first();
        return $punto->nombre_punto_venta;
    }
    
    public function getFechaInicio()
    {
        $formato_fecha = GeneralSetting::first()->date_format;

        $fecha = new Carbon($this->fecha_inicio);
        $fecha = $fecha->format("$formato_fecha H:i");
        return $fecha;
    }

    public function getFechaFin()
    {
        $formato_fecha = GeneralSetting::first()->date_format;

        if ($this->fecha_fin) {
            $fecha = new Carbon($this->fecha_fin);
            $fecha = $fecha->format("$formato_fecha H:i");
            return $fecha;      
        }
    }
}
