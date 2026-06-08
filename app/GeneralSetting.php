<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class GeneralSetting extends Model
{
    protected $fillable =[
        "site_title", "site_logo", "currency", "currency_position", "staff_access", "date_format", "theme", "alert_expiration", "company_id"
    ];
    // Valores por defecto para atributos del modelo
    protected $attributes = [
        'theme' => 'default.css',
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

    public static function current()
    {
        $query = static::query();

        // En contexto autenticado aplicar filtro por empresa; en login/sin sesión usar fallback global.
        if (Schema::hasColumn((new static)->getTable(), 'company_id') && auth()->check()) {
            $query->where('company_id', auth()->user()->company_id);
        }

        return $query->latest()->first();
    }

    public static function currentDateFormat($default = 'd-m-Y')
    {
        $current = static::current();
        return $current->date_format ?? $default;
    }
}
