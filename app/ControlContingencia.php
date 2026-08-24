<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class ControlContingencia extends Model
{
    protected $table = 'control_contingencia';

    protected $fillable =[
        "cuis", 
        "sucursal", 
        "codigo_punto_venta", 
        "cufd_valido", 

        "tipo_factura", 
        "codigo_documento_sector", 
        "codigo_evento", 
        "descripcion", 
        "fecha_inicio_evento", 
        "fecha_fin_evento", 
        "cufd_evento",
        "estado", 

        "codigo_registro_evento", 

        "usuario_modificacion", 
        
        "cantidad_paquetes", 
        "company_id",
    ];

    protected static function boot()
    {
        parent::boot();

        $table = (new static)->getTable();
        if (true) {
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
        $formato_fecha = GeneralSetting::currentDateFormat();

        $fecha = new Carbon($this->fecha_inicio_evento);
        $fecha = $fecha->format("$formato_fecha H:i");
        return $fecha;
    }

    public function getFechaFin()
    {
        $formato_fecha = GeneralSetting::currentDateFormat();

        if ($this->fecha_fin_evento) {
            $fecha = new Carbon($this->fecha_fin_evento);
            $fecha = $fecha->format("$formato_fecha H:i");
            return $fecha;      
        }
    }
}
