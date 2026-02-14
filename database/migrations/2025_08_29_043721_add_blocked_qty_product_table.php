<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

if (!class_exists('AddBlockedQtyProductTable')) {
    class AddBlockedQtyProductTable extends Migration
    {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('product_warehouse')) {
            Schema::table('product_warehouse', function (Blueprint $table) {
                if (!Schema::hasColumn('product_warehouse', 'blocked_qty')) {
                    $table->double('blocked_qty')->default(0)->after('qty');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Eliminar la columna si quieres revertir
        Schema::table('product_warehouse', function (Blueprint $table) {
            if (Schema::hasColumn('product_warehouse', 'blocked_qty')) {
                $table->dropColumn('blocked_qty');
            }
        });
    }
    }
}
