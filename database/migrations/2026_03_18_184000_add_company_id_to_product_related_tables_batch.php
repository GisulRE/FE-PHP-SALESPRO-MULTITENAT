<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCompanyIdToProductRelatedTablesBatch extends Migration
{
    public function up()
    {
        $tables = [
            'pre_sale',
            'product_associated',
            'product_lot',
            'product_pre_sale',
            'product_quotation',
            'product_supplier',
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

        if (
            Schema::hasTable('pre_sale')
            && Schema::hasColumn('pre_sale', 'company_id')
            && Schema::hasColumn('pre_sale', 'customer_id')
            && Schema::hasTable('customers')
            && Schema::hasColumn('customers', 'company_id')
        ) {
            DB::statement('UPDATE pre_sale ps INNER JOIN customers c ON c.id = ps.customer_id SET ps.company_id = c.company_id WHERE ps.company_id IS NULL AND c.company_id IS NOT NULL');
        }

        if (
            Schema::hasTable('pre_sale')
            && Schema::hasColumn('pre_sale', 'company_id')
            && Schema::hasColumn('pre_sale', 'warehouse_id')
            && Schema::hasTable('warehouses')
            && Schema::hasColumn('warehouses', 'company_id')
        ) {
            DB::statement('UPDATE pre_sale ps INNER JOIN warehouses w ON w.id = ps.warehouse_id SET ps.company_id = w.company_id WHERE ps.company_id IS NULL AND w.company_id IS NOT NULL');
        }

        if (
            Schema::hasTable('product_associated')
            && Schema::hasColumn('product_associated', 'company_id')
            && Schema::hasColumn('product_associated', 'product_courtesy_id')
            && Schema::hasTable('products')
            && Schema::hasColumn('products', 'company_id')
        ) {
            DB::statement('UPDATE product_associated pa INNER JOIN products p ON p.id = pa.product_courtesy_id SET pa.company_id = p.company_id WHERE pa.company_id IS NULL AND p.company_id IS NOT NULL');
        }

        if (
            Schema::hasTable('product_associated')
            && Schema::hasColumn('product_associated', 'company_id')
            && Schema::hasColumn('product_associated', 'product_associated_id')
            && Schema::hasTable('products')
            && Schema::hasColumn('products', 'company_id')
        ) {
            DB::statement('UPDATE product_associated pa INNER JOIN products p ON p.id = pa.product_associated_id SET pa.company_id = p.company_id WHERE pa.company_id IS NULL AND p.company_id IS NOT NULL');
        }

        if (
            Schema::hasTable('product_lot')
            && Schema::hasColumn('product_lot', 'company_id')
            && Schema::hasColumn('product_lot', 'idproducto')
            && Schema::hasTable('products')
            && Schema::hasColumn('products', 'company_id')
        ) {
            DB::statement('UPDATE product_lot pl INNER JOIN products p ON p.id = pl.idproducto SET pl.company_id = p.company_id WHERE pl.company_id IS NULL AND p.company_id IS NOT NULL');
        }

        if (
            Schema::hasTable('product_pre_sale')
            && Schema::hasColumn('product_pre_sale', 'company_id')
            && Schema::hasColumn('product_pre_sale', 'presale_id')
            && Schema::hasTable('pre_sale')
            && Schema::hasColumn('pre_sale', 'company_id')
        ) {
            DB::statement('UPDATE product_pre_sale pps INNER JOIN pre_sale ps ON ps.id = pps.presale_id SET pps.company_id = ps.company_id WHERE pps.company_id IS NULL AND ps.company_id IS NOT NULL');
        }

        if (
            Schema::hasTable('product_quotation')
            && Schema::hasColumn('product_quotation', 'company_id')
            && Schema::hasColumn('product_quotation', 'quotation_id')
            && Schema::hasTable('quotations')
            && Schema::hasColumn('quotations', 'company_id')
        ) {
            DB::statement('UPDATE product_quotation pq INNER JOIN quotations q ON q.id = pq.quotation_id SET pq.company_id = q.company_id WHERE pq.company_id IS NULL AND q.company_id IS NOT NULL');
        }

        if (
            Schema::hasTable('product_supplier')
            && Schema::hasColumn('product_supplier', 'company_id')
            && Schema::hasColumn('product_supplier', 'supplier_id')
            && Schema::hasTable('suppliers')
            && Schema::hasColumn('suppliers', 'company_id')
        ) {
            DB::statement('UPDATE product_supplier ps INNER JOIN suppliers s ON s.id = ps.supplier_id SET ps.company_id = s.company_id WHERE ps.company_id IS NULL AND s.company_id IS NOT NULL');
        }

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
                // Ignorar problemas de FK en datos legacy.
            }
        }
    }

    public function down()
    {
        $tables = [
            'pre_sale',
            'product_associated',
            'product_lot',
            'product_pre_sale',
            'product_quotation',
            'product_supplier',
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
