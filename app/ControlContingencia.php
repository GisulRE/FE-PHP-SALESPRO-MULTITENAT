<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ControlContingencia extends Model
{
    protected $table = 'control_contingencia';

    protected $fillable =[
        "cuis", 
        "sucursal", 
        "codigo_punto_venta", 
        "cufd_valido", 

        "tipo_factura", 
        "codigo_documento_sector", 
        "codigo_evento", 
        "descripcion", 
        "fecha_inicio_evento", 
        "fecha_fin_evento", 
        "cufd_evento",
        "estado", 

        "codigo_registro_evento", 

        "usuario_modificacion", 
        
        "cantidad_paquetes", 
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

        $fecha = new Carbon($this->fecha_inicio_evento);
        $fecha = $fecha->format("$formato_fecha H:i");
        return $fecha;
    }

    public function getFechaFin()
    {
        $formato_fecha = GeneralSetting::first()->date_format;

        if ($this->fecha_fin_evento) {
            $fecha = new Carbon($this->fecha_fin_evento);
            $fecha = $fecha->format("$formato_fecha H:i");
            return $fecha;      
        }
    }
}
