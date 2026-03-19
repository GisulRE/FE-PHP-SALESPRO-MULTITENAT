<?php

namespace App;

use App\User;
use Carbon\Carbon;
use App\GeneralSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

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
        "codigo_punto_venta",
        "company_id"
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
