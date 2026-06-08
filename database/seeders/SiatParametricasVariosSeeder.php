<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SiatParametricasVariosSeeder extends Seeder
{
    /**
     * Carga catalogo completo desde el dump SQL compartido.
     */
    public function run()
    {
        if (!Schema::hasTable('siat_parametricas_varios')) {
            return;
        }

        $sourcePath = base_path('siat_parametricas_varios.sql');
        if (!is_file($sourcePath)) {
            return;
        }

        $sql = file_get_contents($sourcePath);
        if ($sql === false) {
            return;
        }

        $insertSql = $this->extractInsertStatement($sql);
        if ($insertSql === null) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('TRUNCATE TABLE siat_parametricas_varios RESTART IDENTITY');
        } else {
            DB::table('siat_parametricas_varios')->delete();
        }

        DB::unprepared($this->normalizeInsertForDriver($insertSql, $driver));

        if ($driver === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('siat_parametricas_varios', 'id'), COALESCE((SELECT MAX(id) FROM siat_parametricas_varios), 1), true)");
        }
    }

    private function extractInsertStatement(string $sql): ?string
    {
        $start = stripos($sql, 'INSERT INTO `siat_parametricas_varios`');
        if ($start === false) {
            $start = stripos($sql, 'INSERT INTO siat_parametricas_varios');
        }

        if ($start === false) {
            return null;
        }

        $end = stripos($sql, "\n\n--", $start);
        if ($end === false) {
            $end = stripos($sql, "\nALTER TABLE", $start);
        }
        if ($end === false) {
            $end = strlen($sql);
        }

        $statement = trim(substr($sql, $start, $end - $start));
        if (!Str::endsWith($statement, ';')) {
            $statement .= ';';
        }

        return $statement;
    }

    private function normalizeInsertForDriver(string $insertSql, string $driver): string
    {
        if ($driver !== 'pgsql') {
            return $insertSql;
        }

        // PostgreSQL no acepta backticks ni secuencias de escape de MySQL.
        $normalized = str_replace('`', '"', $insertSql);
        $normalized = str_replace('\\\\', '\\', $normalized);
        $normalized = preg_replace('/\\bINSERT\\s+INTO\\s+"siat_parametricas_varios"\\b/i', 'INSERT INTO siat_parametricas_varios', $normalized);

        return (string) Str::of($normalized)->trim();
    }
}
