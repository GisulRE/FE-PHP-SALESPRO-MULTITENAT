<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKardexTable extends Migration
{
    public function up()
    {
        // NOTA: Esta migración está deshabilitada porque kardex es una VISTA, no una tabla.
        // La vista se crea en la migración 2026_02_14_130070_create_kardex_view.php
        
        // No hacer nada aquí para evitar conflictos con la vista
    }

    public function down()
    {
        // No hacer nada - la vista se elimina en su propia migración
    }
}
