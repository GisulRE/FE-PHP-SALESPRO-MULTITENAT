<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AutorizacionFacturacion extends Model
{
    protected $table = 'autorizacion_facturacion';

    protected $fillable =[

        "ambiente",
        "codigo_sistema", 
        "estado", 

        "fecha_solicitud", 
        "fecha_vencimiento_token", 
        "token", 

        "tipo_modalidad", 
        "tipo_sistema", 

        "usuario_alta", 
        "usuario_modificacion", 
        "id_empresa", 
        
        "id_url_produccion_obtencion_codigos", 
        "id_url_produccion_operaciones", 
        "id_url_produccion_recepcion_compras", 
        "id_url_produccion_sincronizacion_datos", 
        
        "id_url_pruebas_obtencion_codigos", 
        "id_url_pruebas_operaciones", 
        "id_url_pruebas_recepcion_compras", 
        "id_url_pruebas_sincronizacion_datos", 
    ];

    public function getAmbiente()
    {
        $ambiente = $this->ambiente;
        if ($ambiente == 1 ) {
            return "PRODUCCIÓN";
        }else {
            return "PRUEBAS";
        }
    }
    public function getEstado()
    {
        $estado = $this->estado;
        if ($estado == 1 ) {
            return "Alta";
        }
        elseif ($estado == 2) {
            return "Baja ";
        }
    }

    public function getModalidad()
    {
        $modalidad = $this->tipo_modalidad;
        if ($modalidad == 1 ) {
            return "ELECTRONICA";
        }
        if ($modalidad == 2) {
            return "COMPUTARIZADA";
        }
        
    }

    public function getFechaVencimientoToken()
    {
        $formato_fecha = GeneralSetting::first()->date_format;

        $fecha = new Carbon($this->fecha_vencimiento_token);
        $fecha = $fecha->format("$formato_fecha H:i");
        return $fecha;
    }
    public function getDiasRestantes()
    {
        $fecha_actual = new Carbon();
        $dias_restantes = $fecha_actual->diffInDays($this->fecha_vencimiento_token, false);
        return $dias_restantes;
    }

    public function getFechaModalidad()
    {
        $formato_fecha = GeneralSetting::first()->date_format;

        $fecha = new Carbon($this->fecha_solicitud);
        $fecha = $fecha->format($formato_fecha);
        return $fecha;
    }

    public function getSistema()
    {
        $sistema = $this->tipo_sistema;
        if ($sistema == 1 ) {
            return "Propio";
        }
        elseif ($sistema == 2) {
            return "Proveedor";
        }
    }
}
