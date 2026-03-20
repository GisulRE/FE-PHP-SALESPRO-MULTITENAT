<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncMissingForeignKeys extends Command
{
    protected $signature = 'import:sync-missing-fks {--dry-run : Solo reporta cambios, no aplica ALTER TABLE}';

    protected $description = 'Crea claves foraneas faltantes detectadas por columnas *_id cuando es seguro aplicarlas';

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
            "SELECT c.TABLE_NAME, c.COLUMN_NAME, c.IS_NULLABLE, c.COLUMN_TYPE
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

        $created = [];
        $skipped = [];

        foreach ($missing as $row) {
            $table = $row->TABLE_NAME;
            $column = $row->COLUMN_NAME;
            $nullable = $row->IS_NULLABLE === 'YES';
            $childType = strtolower((string) $row->COLUMN_TYPE);

            $referencedTable = $this->inferReferencedTable($column, $tableNames);
            if ($referencedTable === null || !Schema::hasTable($referencedTable) || !Schema::hasColumn($referencedTable, 'id')) {
                $skipped[] = "$table.$column -> (sin tabla padre inferida)";
                continue;
            }

            $parentTypeRow = DB::selectOne(
                "SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = 'id' LIMIT 1",
                [$database, $referencedTable]
            );

            if (!$parentTypeRow) {
                $skipped[] = "$table.$column -> $referencedTable.id (columna id no encontrada)";
                continue;
            }

            $parentType = strtolower((string) $parentTypeRow->COLUMN_TYPE);
            if ($childType !== $parentType) {
                $skipped[] = "$table.$column -> $referencedTable.id (tipo incompatible: $childType != $parentType)";
                continue;
            }

            $existing = DB::selectOne(
                "SELECT CONSTRAINT_NAME
                 FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = ?
                   AND TABLE_NAME = ?
                   AND COLUMN_NAME = ?
                   AND REFERENCED_TABLE_NAME IS NOT NULL
                 LIMIT 1",
                [$database, $table, $column]
            );

            if ($existing) {
                continue;
            }

            $orphanCount = (int) DB::table($table . ' as c')
                ->leftJoin($referencedTable . ' as p', 'c.' . $column, '=', 'p.id')
                ->whereNotNull('c.' . $column)
                ->whereNull('p.id')
                ->count();

            if ($orphanCount > 0) {
                if ($nullable) {
                    if (!$dryRun) {
                        DB::table($table . ' as c')
                            ->leftJoin($referencedTable . ' as p', 'c.' . $column, '=', 'p.id')
                            ->whereNotNull('c.' . $column)
                            ->whereNull('p.id')
                            ->update(['c.' . $column => null]);
                    }
                } else {
                    $skipped[] = "$table.$column -> $referencedTable.id (huerfanos: $orphanCount, columna no nullable)";
                    continue;
                }
            }

            $indexName = 'idx_' . $table . '_' . $column;
            $fkName = $this->buildConstraintName($table, $column);

            $indexExists = DB::selectOne(
                "SELECT INDEX_NAME FROM information_schema.STATISTICS
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1",
                [$database, $table, $indexName]
            );

            try {
                if (!$dryRun) {
                    if (!$indexExists) {
                        DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$indexName}` (`{$column}`)");
                    }

                    $onDelete = $nullable ? 'SET NULL' : 'RESTRICT';
                    DB::statement(
                        "ALTER TABLE `{$table}`
                         ADD CONSTRAINT `{$fkName}`
                         FOREIGN KEY (`{$column}`)
                         REFERENCES `{$referencedTable}`(`id`)
                         ON UPDATE CASCADE
                         ON DELETE {$onDelete}"
                    );
                }

                $created[] = "$table.$column -> $referencedTable.id";
            } catch (\Throwable $exception) {
                $skipped[] = "$table.$column -> $referencedTable.id (error: " . $exception->getMessage() . ')';
            }
        }

        $this->line('');
        $this->info('FK creadas/aplicables: ' . count($created));
        foreach ($created as $line) {
            $this->line('  + ' . $line);
        }

        $this->line('');
        $this->warn('FK omitidas: ' . count($skipped));
        foreach ($skipped as $line) {
            $this->line('  - ' . $line);
        }

        return 0;
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

    protected function buildConstraintName($table, $column)
    {
        $base = 'fk_' . $table . '_' . $column;
        if (strlen($base) <= 60) {
            return $base;
        }

        return substr($base, 0, 45) . '_' . substr(md5($base), 0, 14);
    }
}
