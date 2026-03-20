<?php

namespace App;

use App\SiatParametricaVario;
use App\SiatActividadEconomica;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class SiatDocumentoSector extends Model
{
    protected $table = 'siat_documento_sector';

    protected $fillable =[

        "codigo_actividad",
        "codigo_documento_sector", 
        "tipo_documento_sector", 
        "usuario_alta",
        "usuario_modificacion",
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

    public function activity()
    {
        return $this->belongsTo(SiatActividadEconomica::class, 'codigo_actividad', 'codigo_caeb');
    }


    public function getDescripcionDocumento()
    {
        return SiatParametricaVario::where('tipo_clasificador', 'tipoDocumentoSector')->
                        where('codigo_clasificador', $this->codigo_documento_sector)->pluck('descripcion')->first();
    }

    public function getPuntoVenta()
    {
        return $this->belongsTo(SiatPuntoVenta::class, 'codigo_punto_venta', 'codigo_punto_venta' );
    }
    public function getSucursal()
    {
        return $this->belongsTo(SiatSucursal::class, 'sucursal', 'sucursal' );
    }

}
