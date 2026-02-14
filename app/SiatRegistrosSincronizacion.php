<?php

namespace App;

use App\User;
use Carbon\Carbon;
use App\GeneralSetting;
use Illuminate\Database\Eloquent\Model;

class SiatRegistrosSincronizacion extends Model
{
    protected $table = 'registros_sincronizacion_siat';

    protected $fillable =[

        "descripcion",
        "operacion", 
        "estado", 
        "usuario_alta", 
        "usuario_modificacion",
        "sucursal",
        "codigo_punto_venta"
    ];

    public function getUsuario()
    {
        return User::where('id', $this->usuario_modificacion)->first()['name'];
    }
    
    public function getFecha()
    {
        $formato_fecha = GeneralSetting::first()->date_format;
        $fecha = new Carbon($this->updated_at);
        $fecha = $fecha->format("$formato_fecha H:i");
        return $fecha;
    }
}
