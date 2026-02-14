<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FacturaMasivaPaquetes extends Model
{
    protected $table = 'factura_masiva_paquetes';

    protected $fillable =[
        "factura_masiva_id", 
        "cantidad_ventas", 

        "fecha_de_envio", 
        "glosa_nro_factura_inicio_a_fin", 
        "arreglo_ventas", 

        "codigo_recepcion", 
        "respuesta_servicio", 
        "log_errores",  

        "estado", 
    ];
}
