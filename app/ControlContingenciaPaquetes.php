<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ControlContingenciaPaquetes extends Model
{
    protected $table = 'control_contingencia_paquetes';

    protected $fillable =[
        "control_contingencia_id", 
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
