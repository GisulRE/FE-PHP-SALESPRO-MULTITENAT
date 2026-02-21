<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMissingColumnsPosSettingTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('pos_setting')) {
            return;
        }

        Schema::table('pos_setting', function (Blueprint $table) {
            if (!Schema::hasColumn('pos_setting', 't_c'))
                $table->text('t_c')->nullable()->after('keybord_active');
            if (!Schema::hasColumn('pos_setting', 'print'))
                $table->tinyInteger('print')->default(0)->after('t_c');
            if (!Schema::hasColumn('pos_setting', 'type_print'))
                $table->string('type_print', 50)->nullable()->after('print');
            if (!Schema::hasColumn('pos_setting', 'date_sell'))
                $table->tinyInteger('date_sell')->default(0)->after('type_print');
            if (!Schema::hasColumn('pos_setting', 'print_order'))
                $table->tinyInteger('print_order')->default(0)->after('date_sell');
            if (!Schema::hasColumn('pos_setting', 'print_presale'))
                $table->tinyInteger('print_presale')->default(0)->after('print_order');
            if (!Schema::hasColumn('pos_setting', 'hour_resetshift'))
                $table->string('hour_resetshift', 10)->nullable()->after('print_presale');
            if (!Schema::hasColumn('pos_setting', 'facturacion_id'))
                $table->unsignedBigInteger('facturacion_id')->nullable()->after('hour_resetshift');
            if (!Schema::hasColumn('pos_setting', 'codigo_emision'))
                $table->string('codigo_emision', 50)->nullable()->after('facturacion_id');
            if (!Schema::hasColumn('pos_setting', 'tipo_moneda_siat'))
                $table->string('tipo_moneda_siat', 50)->nullable()->after('codigo_emision');
            if (!Schema::hasColumn('pos_setting', 'nit_emisor'))
                $table->string('nit_emisor', 50)->nullable()->after('tipo_moneda_siat');
            if (!Schema::hasColumn('pos_setting', 'razon_social_emisor'))
                $table->string('razon_social_emisor')->nullable()->after('nit_emisor');
            if (!Schema::hasColumn('pos_setting', 'direccion_emisor'))
                $table->string('direccion_emisor')->nullable()->after('razon_social_emisor');
            if (!Schema::hasColumn('pos_setting', 'user_siat'))
                $table->string('user_siat')->nullable()->after('direccion_emisor');
            if (!Schema::hasColumn('pos_setting', 'pass_siat'))
                $table->string('pass_siat')->nullable()->after('user_siat');
            if (!Schema::hasColumn('pos_setting', 'url_siat'))
                $table->string('url_siat')->nullable()->after('pass_siat');
            if (!Schema::hasColumn('pos_setting', 'url_operaciones'))
                $table->string('url_operaciones')->nullable()->after('url_siat');
            if (!Schema::hasColumn('pos_setting', 'url_optimo'))
                $table->string('url_optimo')->nullable()->after('url_operaciones');
            if (!Schema::hasColumn('pos_setting', 'url_cobranza'))
                $table->string('url_cobranza')->nullable()->after('url_optimo');
            if (!Schema::hasColumn('pos_setting', 'cant_max_contingencia'))
                $table->integer('cant_max_contingencia')->default(500)->after('url_cobranza');
            if (!Schema::hasColumn('pos_setting', 'cant_max_masiva'))
                $table->integer('cant_max_masiva')->default(500)->after('cant_max_contingencia');
            if (!Schema::hasColumn('pos_setting', 'quotation_printer'))
                $table->tinyInteger('quotation_printer')->default(0)->after('cant_max_masiva');
            if (!Schema::hasColumn('pos_setting', 'customer_sucursal'))
                $table->tinyInteger('customer_sucursal')->default(0)->after('quotation_printer');
            if (!Schema::hasColumn('pos_setting', 'user_category'))
                $table->tinyInteger('user_category')->default(0)->after('customer_sucursal');
            if (!Schema::hasColumn('pos_setting', 'cant_decimal'))
                $table->tinyInteger('cant_decimal')->default(2)->after('user_category');
        });
    }

    public function down()
    {
        $cols = [
            't_c', 'print', 'type_print', 'date_sell', 'print_order', 'print_presale',
            'hour_resetshift', 'facturacion_id', 'codigo_emision', 'tipo_moneda_siat',
            'nit_emisor', 'razon_social_emisor', 'direccion_emisor',
            'user_siat', 'pass_siat', 'url_siat', 'url_operaciones', 'url_optimo', 'url_cobranza',
            'cant_max_contingencia', 'cant_max_masiva', 'quotation_printer',
            'customer_sucursal', 'user_category', 'cant_decimal',
        ];
        Schema::table('pos_setting', function (Blueprint $table) use ($cols) {
            foreach ($cols as $col) {
                if (Schema::hasColumn('pos_setting', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
