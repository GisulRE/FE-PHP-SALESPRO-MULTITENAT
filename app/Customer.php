<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable =[
    "customer_group_id", "name", "company_name", "price_type",
    "email", "phone_number", "tax_no", "address", "city",
    "state", "postal_code", "country", "deposit", "expense",
    "credit", "is_credit", "is_tasadignidad", "is_ley1886",
    "porcentaje_tasadignidad", "porcentaje_ley1886", "is_active", 
    "tipo_documento",
    "valor_documento",
    "complemento_documento",
    "razon_social",
    "codigofijo",
    "nro_medidor",
    'sucursal_id',
    'date_birh'
    ];

    public function getDescripcionTipoDocumento()
    {
        $tipo_documento_identidad_lookup = [
            1 => 'CEDULA DE IDENTIDAD',
            2 => 'CEDULA DE IDENTIDAD DE EXTRANJERO',
            3 => 'PASAPORTE',
            4 => 'OTRO DOCUMENTO DE IDENTIDAD',
            5 => 'NIT ',
        ];
        $descripcion_documento = $tipo_documento_identidad_lookup[ $this->tipo_documento ];

        return $descripcion_documento;
    }
}
