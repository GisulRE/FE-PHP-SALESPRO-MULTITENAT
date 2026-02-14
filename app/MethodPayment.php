<?php

namespace App;

use App\SiatParametricaVario;
use Illuminate\Database\Eloquent\Model;

class MethodPayment extends Model
{
    protected $table = 'method_payments';

    protected $fillable =[

        "name", "description", "apply",
        "codigo_clasificador_siat"
    ];

    public function getDescripcionCodigoClasificador()
    {
        $dato = $this->codigo_clasificador_siat;
        return SiatParametricaVario::where('tipo_clasificador','tipoMetodoPago')->where('codigo_clasificador',$dato)->pluck('descripcion')->first();
        
    }
}
