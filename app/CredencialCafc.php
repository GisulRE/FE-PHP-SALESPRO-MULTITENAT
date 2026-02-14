<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CredencialCafc extends Model
{
    protected $table = 'credenciales_cafc';

    protected $fillable =[
        "año", 
        "tipo_factura", 
        "codigo_documento_sector", 
        "codigo_cafc", 
        "sucursal", 
        "codigo_punto_venta", 
        
        "fecha_emision", 
        "fecha_vigencia", 
        "nro_min", 
        "nro_max", 
        
        "correlativo_factura", 
        "is_active", 
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
}
