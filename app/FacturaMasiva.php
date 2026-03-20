<?php

namespace App;

use Carbon\Carbon;
use App\GeneralSetting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class FacturaMasiva extends Model
{
    protected $table = 'factura_masiva';

    protected $fillable =[

        "glosa", 
        "fecha_inicio", 
        "fecha_fin", 
        
        "tipo_factura", 
        "estado", 
        "cantidad_paquetes", 

        "cuis", 
        "sucursal", 
        "codigo_punto_venta", 
        "codigo_documento_sector", 
        "created_by",
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

    public function getNombreSucursal()
    {
        $sucursal = SiatSucursal::where('sucursal', $this->sucursal)->first();
        return $sucursal->nombre;
    }

    public function getNombrePuntoVenta()
    {
        $punto = SiatPuntoVenta::where('codigo_punto_venta', $this->codigo_punto_venta)->where('sucursal', $this->sucursal)->first();
        return $punto->nombre_punto_venta;
    }
    
    public function getFechaInicio()
    {
        $formato_fecha = GeneralSetting::first()->date_format;

        $fecha = new Carbon($this->fecha_inicio);
        $fecha = $fecha->format("$formato_fecha H:i");
        return $fecha;
    }

    public function getFechaFin()
    {
        $formato_fecha = GeneralSetting::first()->date_format;

        if ($this->fecha_fin) {
            $fecha = new Carbon($this->fecha_fin);
            $fecha = $fecha->format("$formato_fecha H:i");
            return $fecha;      
        }
    }
}
