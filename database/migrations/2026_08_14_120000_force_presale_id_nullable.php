<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ForcePresaleIdNullable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('sales')) {
            if (DB::getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
                DB::statement('ALTER TABLE `sales` MODIFY `presale_id` INT(11) NULL;');
                DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('sales')) {
            if (DB::getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
                DB::statement('ALTER TABLE `sales` MODIFY `presale_id` INT(11) NOT NULL;');
                DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
            }
        }
    }
}
