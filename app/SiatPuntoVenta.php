<?php

namespace App;

use App\Biller;
use Carbon\Carbon;
use App\SiatSucursal;
use Illuminate\Database\Eloquent\Model;

class SiatPuntoVenta extends Model
{
    protected $table = 'puntos_venta';

    protected $fillable =[

        "codigo_punto_venta",
        "nombre_punto_venta", 
        "descripcion",
        "tipo_punto_venta", 
        "codigo_cuis",
        "fecha_vigencia_cuis",
        "usuario_alta",
        "sucursal",
        "correlativo_factura",
        "correlativo_alquiler",
        "correlativo_servicios_basicos",
        "correlativo_nota_debcred",
        "modo_contingencia", 
        "fecha_inicio",
        "fecha_fin",
        "nit_comisionista",
        "numero_contrato",
        "is_siat",
        "is_active"
    ];

    public function biller()
    {
        return $this->belongsTo(Biller::class, 'codigo_punto_venta', 'punto_venta_siat');
    }

    public function getFecha()
    {
        $formato_fecha = GeneralSetting::first()->date_format;

        $fecha = new Carbon($this->fecha_vigencia_cuis);
        $fecha = $fecha->format("$formato_fecha H:i");
        return $fecha;
    }
    public function getTipoVenta()
    {
        $status = $this->tipo_punto_venta;
        if ($status == 1 ) {
            return "PUNTO VENTA COMISIONISTA";
        }
        if ($status == 2 ) {
            return "PUNTO VENTA VENTANILLA DE COBRANZA";
        }
        if ($status == 3 ) {
            return "PUNTO DE VENTA MOVILES";
        }
        if ($status == 4 ) {
            return "PUNTO DE VENTA YPFB";
        }
        if ($status == 5 ) {
            return "PUNTO DE VENTA CAJEROS";
        }
        if ($status == 6 ) {
            return "PUNTO DE VENTA CONJUNTA";
        }
    }

    public function getNombreSucursal()
    {
        $sucursal = SiatSucursal::where('sucursal', $this->sucursal)->first();
        return $sucursal->nombre;
    }
}
