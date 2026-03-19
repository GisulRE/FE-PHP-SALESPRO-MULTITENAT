<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NormalizeForeignKeyColumnTypes extends Command
{
    protected $signature = 'import:normalize-fk-types {--dry-run : Solo reporta, no modifica columnas}';

    protected $description = 'Normaliza tipos de columnas *_id para que coincidan con la PK referenciada y habilitar creacion de FKs';

    protected $referenceMap = [
        'company' => 'companies',
        'user' => 'users',
        'role' => 'roles',
        'warehouse' => 'warehouses',
        'biller' => 'billers',
        'supplier' => 'suppliers',
        'category' => 'categories',
        'customer' => 'customers',
        'customer_group' => 'customer_groups',
        'department' => 'departments',
        'employee' => 'employees',
        'coupon' => 'coupons',
        'product' => 'products',
        'tax' => 'taxes',
        'purchase_unit' => 'units',
        'sale_unit' => 'units',
        'variant' => 'product_variants',
        'presale' => 'pre_sale',
        'attentionshift' => 'attention_shift',
        'facturacion' => 'autorizacion_facturacion',
        'methodpay' => 'method_payments',
        'whatsapp_session' => 'whatsapp_sessions',
        'sucursal' => 'sucursal_siat',
    ];

    public function handle()
    {
        $dryRun = (bool) $this->option('dry-run');
        $database = config('database.connections.mysql.database');

        $missing = DB::select(
            "SELECT c.TABLE_NAME, c.COLUMN_NAME, c.COLUMN_TYPE, c.IS_NULLABLE
             FROM information_schema.COLUMNS c
             LEFT JOIN information_schema.KEY_COLUMN_USAGE k
               ON k.TABLE_SCHEMA = c.TABLE_SCHEMA
              AND k.TABLE_NAME = c.TABLE_NAME
              AND k.COLUMN_NAME = c.COLUMN_NAME
              AND k.REFERENCED_TABLE_NAME IS NOT NULL
             WHERE c.TABLE_SCHEMA = ?
               AND c.COLUMN_NAME LIKE '%\\_id' ESCAPE '\\\\'
               AND c.COLUMN_NAME <> 'id'
               AND k.COLUMN_NAME IS NULL
             ORDER BY c.TABLE_NAME, c.COLUMN_NAME",
            [$database]
        );

        $tables = DB::select(
            "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE'",
            [$database]
        );

        $tableNames = [];
        foreach ($tables as $table) {
            $tableNames[] = $table->TABLE_NAME;
        }

        $updated = [];
        $skipped = [];

        foreach ($missing as $row) {
            $table = $row->TABLE_NAME;
            $column = $row->COLUMN_NAME;
            $childType = strtolower((string) $row->COLUMN_TYPE);
            $nullable = $row->IS_NULLABLE === 'YES';

            $referencedTable = $this->inferReferencedTable($column, $tableNames);
            if ($referencedTable === null || !Schema::hasTable($referencedTable) || !Schema::hasColumn($referencedTable, 'id')) {
                $skipped[] = "$table.$column (sin tabla padre inferida)";
                continue;
            }

            $parentInfo = DB::selectOne(
                "SELECT COLUMN_TYPE
                 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = 'id' LIMIT 1",
                [$database, $referencedTable]
            );

            if (!$parentInfo) {
                $skipped[] = "$table.$column (sin parent id)";
                continue;
            }

            $parentType = strtolower((string) $parentInfo->COLUMN_TYPE);
            if ($childType === $parentType) {
                continue;
            }

            if (!$this->isIntegerType($childType) || !$this->isIntegerType($parentType)) {
                $skipped[] = "$table.$column ($childType -> $parentType no entero compatible)";
                continue;
            }

            if (strpos($parentType, 'unsigned') !== false) {
                $negativeCount = (int) DB::table($table)
                    ->whereNotNull($column)
                    ->where($column, '<', 0)
                    ->count();
                if ($negativeCount > 0) {
                    $skipped[] = "$table.$column (valores negativos: $negativeCount)";
                    continue;
                }
            }

            $nullClause = $nullable ? 'NULL' : 'NOT NULL';
            $sql = "ALTER TABLE `{$table}` MODIFY COLUMN `{$column}` {$parentType} {$nullClause}";

            try {
                if (!$dryRun) {
                    DB::statement($sql);
                }
                $updated[] = "$table.$column: $childType -> $parentType";
            } catch (\Throwable $exception) {
                $skipped[] = "$table.$column (error: " . $exception->getMessage() . ')';
            }
        }

        $this->line('');
        $this->info('Columnas normalizadas: ' . count($updated));
        foreach ($updated as $line) {
            $this->line('  + ' . $line);
        }

        $this->line('');
        $this->warn('Columnas omitidas: ' . count($skipped));
        foreach ($skipped as $line) {
            $this->line('  - ' . $line);
        }

        return 0;
    }

    protected function isIntegerType($columnType)
    {
        return (bool) preg_match('/^(tinyint|smallint|mediumint|int|bigint)(\\([0-9]+\\))?( unsigned)?$/', trim(strtolower((string) $columnType)));
    }

    protected function inferReferencedTable($column, array $tableNames)
    {
        if (!preg_match('/^([a-z0-9_]+)_id$/i', (string) $column, $matches)) {
            return null;
        }

        $base = strtolower($matches[1]);
        if ($base === 'id') {
            return null;
        }

        if (isset($this->referenceMap[$base]) && in_array($this->referenceMap[$base], $tableNames, true)) {
            return $this->referenceMap[$base];
        }

        $candidates = [$base, $base . 's', $base . 'es'];
        if (substr($base, -1) === 'y' && strlen($base) > 1) {
            $candidates[] = substr($base, 0, -1) . 'ies';
        }

        $candidates = array_values(array_unique($candidates));
        foreach ($candidates as $candidate) {
            if (in_array($candidate, $tableNames, true)) {
                return $candidate;
            }
        }

        $normalizedBase = preg_replace('/[^a-z0-9]/', '', $base);
        foreach ($tableNames as $tableName) {
            $normalizedTable = preg_replace('/[^a-z0-9]/', '', strtolower($tableName));
            $normalizedSingular = preg_replace('/(ies|s)$/', '', $normalizedTable);
            if ($normalizedBase === $normalizedTable || $normalizedBase === $normalizedSingular) {
                return $tableName;
            }
        }

        return null;
    }
}
