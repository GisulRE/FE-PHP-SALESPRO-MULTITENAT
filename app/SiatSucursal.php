<?php

namespace App;

use App\Warehouse;
use App\AutorizacionFacturacion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

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
        'company_id',
    ];

    protected static function boot()
    {
        parent::boot();

        $table = (new static)->getTable();
        if (Schema::hasColumn($table, 'company_id')) {
            static::addGlobalScope('company', function (Builder $builder) use ($table) {
                if (auth()->check()) {
                    $builder->where($table . '.company_id', auth()->user()->company_id);
                }
            });

            static::creating(function ($model) {
                if (auth()->check() && empty($model->company_id)) {
                    $model->company_id = auth()->user()->company_id;
                }
            });
        }
    }

    public function almacen()
    {
        return $this->hasOne(Warehouse::class, 'sucursal_id', 'id');
    }

    public function autorizacionFacturacion()
    {
        return $this->belongsTo(AutorizacionFacturacion::class, 'id_autorizacion_facturacion', 'id');
    }
}
