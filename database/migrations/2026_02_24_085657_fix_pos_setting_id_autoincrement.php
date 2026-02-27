<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixPosSettingIdAutoincrement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Eliminar registros con id=0 que causan conflictos
        \DB::table('pos_setting')->where('id', 0)->delete();
        
        // Cambiar el índice UNIQUE por PRIMARY KEY
        // La columna ya tiene AUTO_INCREMENT, solo falta la PRIMARY KEY
        \DB::statement('ALTER TABLE pos_setting DROP INDEX pos_setting_id_unique, ADD PRIMARY KEY (id)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revertir el cambio
        \DB::statement('ALTER TABLE pos_setting DROP PRIMARY KEY, ADD UNIQUE KEY pos_setting_id_unique (id)');
    }
}
