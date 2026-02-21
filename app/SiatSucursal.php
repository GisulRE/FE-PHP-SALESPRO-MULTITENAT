<?php

namespace App;

use App\Warehouse;
use App\AutorizacionFacturacion;
use Illuminate\Database\Eloquent\Model;

class SiatSucursal extends Model
{
    protected $table = 'sucursal_siat';

    // Ajustado a las columnas reales existentes en la tabla `sucursal_siat`
    protected $fillable = [
        'codigo',
        'nombre',
        'direccion',
        'telefono',
        'ciudad',
        'estado',
        'empresa_id',
        'departamento',
        'email'
    ];

    public function almacen()
    {
        return $this->belongsTo(Warehouse::class, 'id', 'sucursal_id');
    }

    public function getCodigoAutorizacion()
    {
        return $this->belongsTo(AutorizacionFacturacion::class, 'id_autorizacion_facturacion', 'id');;
    }
}
