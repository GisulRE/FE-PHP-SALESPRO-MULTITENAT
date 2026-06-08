<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCompanyIdToFirstTenTables extends Migration
{
    public function up()
    {
        $driver = DB::getDriverName();
        $runUpdateFromJoin = function ($mysqlSql, $pgsqlSql) use ($driver) {
            DB::statement($driver === 'pgsql' ? $pgsqlSql : $mysqlSql);
        };

        $tables = [
            'account_method_pay',
            'adjustment_accounts',
            'autorizacion_facturacion',
            'biller_warehouses',
            'cashier',
            'control_contingencia',
            'control_contingencia_paquetes',
            'credenciales_cafc',
            'customer_company',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            if (!Schema::hasColumn($table, 'company_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->unsignedBigInteger('company_id')->nullable()->after('id')->index();
                });
            }
        }

        // Backfill por relacion directa cuando es posible.
        if (
            Schema::hasTable('account_method_pay')
            && Schema::hasTable('accounts')
            && Schema::hasColumn('account_method_pay', 'company_id')
            && Schema::hasColumn('accounts', 'company_id')
        ) {
            $runUpdateFromJoin(
                'UPDATE account_method_pay amp INNER JOIN accounts a ON a.id = amp.account_id SET amp.company_id = a.company_id WHERE amp.company_id IS NULL AND a.company_id IS NOT NULL',
                'UPDATE account_method_pay amp SET company_id = a.company_id FROM accounts a WHERE a.id = amp.account_id AND amp.company_id IS NULL AND a.company_id IS NOT NULL'
            );
        }

        if (
            Schema::hasTable('adjustment_accounts')
            && Schema::hasTable('accounts')
            && Schema::hasColumn('adjustment_accounts', 'company_id')
            && Schema::hasColumn('accounts', 'company_id')
        ) {
            $runUpdateFromJoin(
                'UPDATE adjustment_accounts aa INNER JOIN accounts a ON a.id = aa.account_id SET aa.company_id = a.company_id WHERE aa.company_id IS NULL AND a.company_id IS NOT NULL',
                'UPDATE adjustment_accounts aa SET company_id = a.company_id FROM accounts a WHERE a.id = aa.account_id AND aa.company_id IS NULL AND a.company_id IS NOT NULL'
            );
        }

        if (
            Schema::hasTable('biller_warehouses')
            && Schema::hasTable('billers')
            && Schema::hasColumn('biller_warehouses', 'company_id')
            && Schema::hasColumn('billers', 'company_id')
        ) {
            $runUpdateFromJoin(
                'UPDATE biller_warehouses bw INNER JOIN billers b ON b.id = bw.biller_id SET bw.company_id = b.company_id WHERE bw.company_id IS NULL AND b.company_id IS NOT NULL',
                'UPDATE biller_warehouses bw SET company_id = b.company_id FROM billers b WHERE b.id = bw.biller_id AND bw.company_id IS NULL AND b.company_id IS NOT NULL'
            );
        }

        if (
            Schema::hasTable('cashier')
            && Schema::hasTable('accounts')
            && Schema::hasColumn('cashier', 'company_id')
            && Schema::hasColumn('accounts', 'company_id')
        ) {
            $runUpdateFromJoin(
                'UPDATE cashier c INNER JOIN accounts a ON a.id = c.account_id SET c.company_id = a.company_id WHERE c.company_id IS NULL AND a.company_id IS NOT NULL',
                'UPDATE cashier c SET company_id = a.company_id FROM accounts a WHERE a.id = c.account_id AND c.company_id IS NULL AND a.company_id IS NOT NULL'
            );
        }

        if (
            Schema::hasTable('customer_company')
            && Schema::hasTable('customers')
            && Schema::hasColumn('customer_company', 'company_id')
            && Schema::hasColumn('customers', 'company_id')
        ) {
            $runUpdateFromJoin(
                'UPDATE customer_company cc INNER JOIN customers c ON c.id = cc.customer_id SET cc.company_id = c.company_id WHERE cc.company_id IS NULL AND c.company_id IS NOT NULL',
                'UPDATE customer_company cc SET company_id = c.company_id FROM customers c WHERE c.id = cc.customer_id AND cc.company_id IS NULL AND c.company_id IS NOT NULL'
            );
        }

        if (
            Schema::hasTable('autorizacion_facturacion')
            && Schema::hasColumn('autorizacion_facturacion', 'company_id')
            && Schema::hasColumn('autorizacion_facturacion', 'id_empresa')
            && Schema::hasTable('companies')
        ) {
            $runUpdateFromJoin(
                'UPDATE autorizacion_facturacion af INNER JOIN companies c ON c.id = af.id_empresa SET af.company_id = c.id WHERE af.company_id IS NULL',
                'UPDATE autorizacion_facturacion af SET company_id = c.id FROM companies c WHERE c.id = af.id_empresa AND af.company_id IS NULL'
            );
        }

        if (Schema::hasTable('companies') && Schema::hasColumn('companies', 'company_id')) {
            DB::statement('UPDATE companies SET company_id = id WHERE company_id IS NULL');
        }

        $defaultCompanyId = null;
        if (Schema::hasTable('companies')) {
            $defaultCompanyId = DB::table('companies')->orderBy('id')->value('id');
        }

        if ($defaultCompanyId !== null) {
            foreach ($tables as $table) {
                if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'company_id')) {
                    continue;
                }

                DB::table($table)->whereNull('company_id')->update(['company_id' => $defaultCompanyId]);
            }
        }

        // FK en tablas de negocio; en companies se evita autorreferencia para mantener compatibilidad.
        $fkTables = array_values(array_diff($tables, ['companies']));
        foreach ($fkTables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasTable('companies') || !Schema::hasColumn($table, 'company_id')) {
                continue;
            }

            try {
                Schema::table($table, function (Blueprint $t) {
                    $t->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
                });
            } catch (\Exception $e) {
                // Ignorar errores de FK por diferencias de motor o datos legacy.
            }
        }
    }

    public function down()
    {
        $tables = [
            'account_method_pay',
            'adjustment_accounts',
            'autorizacion_facturacion',
            'biller_warehouses',
            'cashier',
            'companies',
            'control_contingencia',
            'control_contingencia_paquetes',
            'credenciales_cafc',
            'customer_company',
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
