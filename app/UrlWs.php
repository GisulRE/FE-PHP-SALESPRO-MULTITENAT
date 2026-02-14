<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UrlWs extends Model
{
    protected $table = 'url_ws';

    protected $fillable =[

        "ambiente",
        "fecha_alta", 
        "nombre_servicio", 
        "ruta_url", 
        "tipo_url", 
        "uso_modalidad", 

        "usuario_alta", 
        
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
    public function getTipoUrl()
    {
        $tipo_url = $this->tipo_url;
        if ($tipo_url == 1 ) {
            return "Url Documentos";
        }
        elseif ($tipo_url == 2) {
            return "Url Servicios Comunes ";
        }
        else {
            return "sin datos";
        }
    }
    public function getModalidad()
    {
        $modalidad = $this->uso_modalidad;
        if ($modalidad == 1 ) {
            return "ELECTRONICA";
        }
        if ($modalidad == 2) {
            return "COMPUTARIZADA";
        }
        if ($modalidad == 3) {
            return "COMPUTARIZADA/ELECTRONICA";
        }
        
    }
}
