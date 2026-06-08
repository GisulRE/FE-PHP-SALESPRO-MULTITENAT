<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MakePosSettingIdAutoincrement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $driver = DB::getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'pgsql') {
            // Create sequence and set default nextval for Postgres
            DB::statement("CREATE SEQUENCE IF NOT EXISTS pos_setting_id_seq;");
            // set sequence value to max(id) or 1
            DB::statement("SELECT setval('pos_setting_id_seq', COALESCE((SELECT MAX(id) FROM pos_setting), 1));");
            DB::statement("ALTER SEQUENCE pos_setting_id_seq OWNED BY pos_setting.id;");
            DB::statement("ALTER TABLE pos_setting ALTER COLUMN id SET DEFAULT nextval('pos_setting_id_seq');");
        } elseif ($driver === 'mysql') {
            // MySQL: modify column to AUTO_INCREMENT and primary key
            DB::statement("ALTER TABLE pos_setting MODIFY COLUMN id INT NOT NULL AUTO_INCREMENT PRIMARY KEY;");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $driver = DB::getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'pgsql') {
            // Remove default and drop sequence if exists
            DB::statement("ALTER TABLE pos_setting ALTER COLUMN id DROP DEFAULT;");
            DB::statement("DROP SEQUENCE IF EXISTS pos_setting_id_seq;");
        } elseif ($driver === 'mysql') {
            // Can't reliably revert AUTO_INCREMENT without additional context; set to INT NOT NULL
            DB::statement("ALTER TABLE pos_setting MODIFY COLUMN id INT NOT NULL;");
        }
    }
}
