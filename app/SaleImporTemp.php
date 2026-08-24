<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class SaleImporTemp extends Model
{
    protected $table = 'sales_import_temp';

    protected $fillable = [
        'NRO_FACT', 'facturamasiva_id', 'codigoSucursal', 'nombreRazonSocial', 'codigoTipoDocumentoIdentidad','numeroDocumento',
        'complemento', 'codigoCliente', 'mes', 'gestion', 'ciudad', 'zona', 'numero_medidor', 'domicilio_cliente',
        'codigoMetodoPago', 'numeroTarjeta', 'montoTotal', 'montoTotalSujetoIva', 'consumoPeriodo', 'beneficiarioLey1886',
        'montoDescuentoLey1886', 'montoDescuentoTarifaDignidad', 'tasaAseo', 'tasaAlumbrado', 'otrasTasas', 'ajusteNoSujetoIva',
        'detalleAjusteNoSujetoIva', 'ajusteSujetoIva', 'detalleAjusteSujetoIva', 'otrosPagosNoSujetoIva', 'descuentoAdicional',
        'codigoExcepcion', 'cafc', 'usuario', 'codigoProducto', 'descripcion', 'cantidad', 'precioUnitario', 'montoDescuento',
        'subTotal', 'categoria', 'email', 'lectant', 'f_lectAnt', 'lectact', 'f_lectAct', 'company_id'
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
}
