<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCompanyIdToPaymentChildrenTables extends Migration
{
    public function up()
    {
        $driver = DB::getDriverName();

        $tables = [
            'payment_with_credit_card',
            'payment_with_gift_card',
            'payment_with_paypal',
            'payment_with_receivable',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table) || Schema::hasColumn($table, 'company_id')) {
                continue;
            }

            $afterColumn = null;
            if (Schema::hasColumn($table, 'id')) {
                $afterColumn = 'id';
            } else {
                $columns = Schema::getColumnListing($table);
                $afterColumn = !empty($columns) ? $columns[0] : null;
            }

            Schema::table($table, function (Blueprint $t) use ($afterColumn) {
                $column = $t->unsignedBigInteger('company_id')->nullable()->index();
                if ($afterColumn !== null) {
                    $column->after($afterColumn);
                }
            });
        }

        // Backfill por payment_id cuando existe en la tabla hija.
        foreach ($tables as $table) {
            if (
                Schema::hasTable($table)
                && Schema::hasColumn($table, 'company_id')
                && Schema::hasColumn($table, 'payment_id')
                && Schema::hasTable('payments')
                && Schema::hasColumn('payments', 'company_id')
            ) {
                if ($driver === 'pgsql') {
                    DB::statement("UPDATE {$table} t SET company_id = p.company_id FROM payments p WHERE p.id = t.payment_id AND t.company_id IS NULL AND p.company_id IS NOT NULL");
                } else {
                    DB::statement("UPDATE {$table} t INNER JOIN payments p ON p.id = t.payment_id SET t.company_id = p.company_id WHERE t.company_id IS NULL AND p.company_id IS NOT NULL");
                }
            }
        }

        // Fallback al primer company existente para evitar nulls residuales en datos legacy.
        $defaultCompanyId = null;
        if (Schema::hasTable('companies')) {
            $defaultCompanyId = DB::table('companies')->orderBy('id')->value('id');
        }

        if ($defaultCompanyId !== null) {
            foreach ($tables as $table) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, 'company_id')) {
                    DB::table($table)->whereNull('company_id')->update(['company_id' => $defaultCompanyId]);
                }
            }
        }

        foreach ($tables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'company_id') || !Schema::hasTable('companies')) {
                continue;
            }

            try {
                Schema::table($table, function (Blueprint $t) {
                    $t->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
                });
            } catch (\Exception $e) {
                // Ignorar errores de FK por datos legacy.
            }
        }
    }

    public function down()
    {
        $tables = [
            'payment_with_credit_card',
            'payment_with_gift_card',
            'payment_with_paypal',
            'payment_with_receivable',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'company_id')) {
                continue;
            }

            try {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropForeign(['company_id']);
                });
            } catch (\Exception $e) {
                // Ignorar si no existe FK.
            }

            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn('company_id');
            });
        }
    }
}
