<?php

namespace App;

use App\SiatParametricaVario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class MethodPayment extends Model
{
    protected $table = 'method_payments';

    protected $fillable =[

        "name", "description", "apply",
        "codigo_clasificador_siat", "company_id"
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

    public function getDescripcionCodigoClasificador()
    {
        $dato = $this->codigo_clasificador_siat;
        return SiatParametricaVario::where('tipo_clasificador','tipoMetodoPago')->where('codigo_clasificador',$dato)->pluck('descripcion')->first();
        
    }
}
