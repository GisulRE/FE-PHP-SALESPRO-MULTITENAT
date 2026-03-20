<?php

namespace App;
use App\User;
use Carbon\Carbon;
use App\GeneralSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

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
        "company_id",
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

    public function updateEstado()
    {
        $this->update(['estado' => 0]);
    }

}
