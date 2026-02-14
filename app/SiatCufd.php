<?php

namespace App;
use App\User;
use Carbon\Carbon;
use App\GeneralSetting;
use Illuminate\Database\Eloquent\Model;

class SiatCufd extends Model
{
    protected $table = 'siat_cufd';

    protected $fillable =[

        "codigo_cufd",
        "codigo_control", 
        "direccion",
        "fecha_registro",
        "fecha_vigencia",
        
        "sucursal",
        "codigo_punto_venta",

        "estado", 
        "usuario_alta", 
        "id_empresa",
    ];

    public function updateEstado()
    {
        $this->update(['estado' => 0]);
    }

}
