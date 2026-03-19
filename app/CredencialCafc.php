<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class CredencialCafc extends Model
{
    protected $table = 'credenciales_cafc';

    protected $fillable =[
        "año", 
        "tipo_factura", 
        "codigo_documento_sector", 
        "codigo_cafc", 
        "sucursal", 
        "codigo_punto_venta", 
        
        "fecha_emision", 
        "fecha_vigencia", 
        "nro_min", 
        "nro_max", 
        
        "correlativo_factura", 
        "is_active",
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
}
