<?php

namespace App;

use App\Warehouse;
use App\AutorizacionFacturacion;
use Illuminate\Database\Eloquent\Model;

class SiatSucursal extends Model
{
    protected $table = 'sucursal_siat';

    protected $fillable = [
        'sucursal',
        'nombre',
        'descripcion_sucursal',
        'domicilio_tributario',
        'ciudad_municipio',
        'telefono',
        'email',
        'id_autorizacion_facturacion',
        'departamento',
        'estado',
        'usuario_alta',
        'id_empresa',
    ];

    public function almacen()
    {
        return $this->hasOne(Warehouse::class, 'sucursal_id', 'id');
    }

    public function autorizacionFacturacion()
    {
        return $this->belongsTo(AutorizacionFacturacion::class, 'id_autorizacion_facturacion', 'id');
    }
}
