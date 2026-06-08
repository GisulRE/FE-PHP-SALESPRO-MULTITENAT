<?php

namespace App\Services\Import;

use Illuminate\Support\Facades\DB;

class ImportSchemaService
{
    protected $loaded = false;
    protected $tableMetadata = [];
    protected $excludedTables = [
        'migrations',
        'jobs',
        'failed_jobs',
        'sessions',
        'password_resets',
        'websockets_statistics_entries',
        'telescope_entries',
        'telescope_entries_tags',
        'telescope_monitoring',
        'oauth_access_tokens',
        'oauth_auth_codes',
        'oauth_clients',
        'oauth_personal_access_clients',
        'oauth_refresh_tokens',
        'permissions',
        'roles',
        'model_has_permissions',
        'model_has_roles',
        'role_has_permissions',
        'companies',
        'general_settings',
        'pos_setting',
        'languages',
        'import_jobs',
        'import_job_details',
        'import_job_logs',
        'migration_map',
    ];

    protected $naturalKeyPriority = [
        'code',
        'name',
        'title',
        'email',
        'reference_no',
        'valor_documento',
        'phone_number',
        'phone',
    ];

    protected $inferredReferenceMap = [
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

    public function getExcludedTables()
    {
        return $this->excludedTables;
    }

    public function getAllImportableTables()
    {
        $this->loadMetadata();

        return array_values(array_filter(array_keys($this->tableMetadata), function ($table) {
            return !in_array($table, $this->excludedTables, true);
        }));
    }

    public function getTableMetadata($tables = null)
    {
        $this->loadMetadata();

        if ($tables === null) {
            return $this->tableMetadata;
        }

        $filtered = [];
        foreach ($tables as $table) {
            if (isset($this->tableMetadata[$table])) {
                $filtered[$table] = $this->tableMetadata[$table];
            }
        }

        return $filtered;
    }

    public function buildPreview(array $parsedTables)
    {
        $metadata = $this->getTableMetadata(array_keys($parsedTables));
        $comparison = [];
        $warnings = [];

        foreach ($parsedTables as $table => $payload) {
            $tableMeta = isset($metadata[$table]) ? $metadata[$table] : null;
            $dbColumns = $tableMeta ? array_keys($tableMeta['columns']) : [];
            $sqlColumns = isset($payload['columns']) ? $payload['columns'] : [];
            $colDetails = [];

            foreach ($dbColumns as $columnName) {
                $columnMeta = $tableMeta['columns'][$columnName];
                $inSql = in_array($columnName, $sqlColumns, true);
                $critical = !$inSql
                    && $columnMeta['nullable'] === false
                    && $columnMeta['default'] === null
                    && $columnMeta['extra'] !== 'auto_increment';

                $colDetails[] = [
                    'name' => $columnName,
                    'type' => $columnMeta['data_type'],
                    'full_type' => $columnMeta['column_type'],
                    'nullable' => $columnMeta['nullable'],
                    'default' => $columnMeta['default'],
                    'in_sql' => $inSql,
                    'critical' => $critical,
                ];
            }

            $extraSqlCols = array_values(array_diff($sqlColumns, $dbColumns));
            $criticalCount = count(array_filter($colDetails, function ($item) {
                return $item['critical'];
            }));

            $comparison[$table] = [
                'exists' => $tableMeta !== null,
                'row_count' => isset($payload['row_count']) ? $payload['row_count'] : count($payload['rows']),
                'sql_cols' => $sqlColumns,
                'col_details' => $colDetails,
                'extra_sql_cols' => $extraSqlCols,
                'critical_count' => $criticalCount,
                'has_company_id' => $tableMeta ? $tableMeta['has_company_id'] : in_array('company_id', $sqlColumns, true),
                'dependencies' => $tableMeta ? $tableMeta['dependencies'] : [],
            ];

            if (in_array($table, $this->excludedTables, true)) {
                $warnings[] = 'La tabla ' . $table . ' es una tabla de sistema y será omitida.';
            }

            if ($tableMeta && !$tableMeta['has_company_id']) {
                $warnings[] = 'La tabla ' . $table . ' no tiene company_id; se importará solo si sus relaciones permiten aislarla por tenant.';
            }
        }

        $tables = array_keys($parsedTables);

        return [
            'comparison' => $comparison,
            'migration_order' => $this->resolveMigrationOrder($tables),
            'root_tables' => $this->getRootTables($tables),
            'warnings' => array_values(array_unique($warnings)),
        ];
    }

    public function resolveMigrationOrder(array $tables)
    {
        $metadata = $this->getTableMetadata($tables);
        $remainingDependencies = [];
        $selectedTables = array_values(array_unique($tables));

        foreach ($selectedTables as $table) {
            $dependencies = isset($metadata[$table]) ? $metadata[$table]['dependencies'] : [];
            $remainingDependencies[$table] = array_values(array_intersect($dependencies, $selectedTables));
        }

        $queue = [];
        foreach ($remainingDependencies as $table => $dependencies) {
            if (count($dependencies) === 0) {
                $queue[] = $table;
            }
        }
        sort($queue);

        $order = [];
        while (!empty($queue)) {
            $table = array_shift($queue);
            if (in_array($table, $order, true)) {
                continue;
            }

            $order[] = $table;
            foreach ($remainingDependencies as $candidate => $dependencies) {
                if (in_array($table, $dependencies, true)) {
                    $remainingDependencies[$candidate] = array_values(array_diff($dependencies, [$table]));
                    if (count($remainingDependencies[$candidate]) === 0 && !in_array($candidate, $order, true) && !in_array($candidate, $queue, true)) {
                        $queue[] = $candidate;
                    }
                }
            }
            sort($queue);
        }

        if (count($order) !== count($selectedTables)) {
            foreach ($selectedTables as $table) {
                if (!in_array($table, $order, true)) {
                    $order[] = $table;
                }
            }
        }

        return $order;
    }

    public function getRootTables(array $tables = null)
    {
        if ($tables === null) {
            $tables = $this->getAllImportableTables();
        }

        $metadata = $this->getTableMetadata($tables);
        $roots = [];

        foreach ($tables as $table) {
            $dependencies = isset($metadata[$table]) ? array_intersect($metadata[$table]['dependencies'], $tables) : [];
            if (count($dependencies) === 0) {
                $roots[] = $table;
            }
        }

        sort($roots);
        return $roots;
    }

    protected function inferReferencedTableFromColumn($columnName)
    {
        $column = (string) $columnName;
        if (!preg_match('/^([a-z0-9_]+)_id$/i', $column, $matches)) {
            return null;
        }

        $base = strtolower($matches[1]);
        if ($base === 'id') {
            return null;
        }

        if (isset($this->inferredReferenceMap[$base]) && isset($this->tableMetadata[$this->inferredReferenceMap[$base]])) {
            return $this->inferredReferenceMap[$base];
        }

        $candidates = [$base];
        $candidates[] = $base . 's';
        $candidates[] = $base . 'es';
        if (substr($base, -1) === 'y' && strlen($base) > 1) {
            $candidates[] = substr($base, 0, -1) . 'ies';
        }

        $uniqueCandidates = array_values(array_unique($candidates));
        foreach ($uniqueCandidates as $candidate) {
            if (isset($this->tableMetadata[$candidate])) {
                return $candidate;
            }
        }

        $normalizedBase = preg_replace('/[^a-z0-9]/', '', $base);
        foreach (array_keys($this->tableMetadata) as $tableName) {
            $normalizedTable = preg_replace('/[^a-z0-9]/', '', strtolower($tableName));
            $normalizedSingular = preg_replace('/(ies|s)$/', '', $normalizedTable);
            if ($normalizedBase === $normalizedTable || $normalizedBase === $normalizedSingular) {
                return $tableName;
            }
        }

        return null;
    }

    protected function loadMetadata()
    {
        if ($this->loaded) {
            return;
        }

        // Detectar el driver activo para usar la query correcta
        $driver = config('database.default');
        $isPostgres = ($driver === 'pgsql');

        if ($isPostgres) {
            $this->loadMetadataPostgres();
        } else {
            $this->loadMetadataMysql();
        }

        // Inferir dependencias por columnas *_id cuando las FK no están declaradas físicamente
        foreach ($this->tableMetadata as $table => $meta) {
            $existingForeignKeyColumns = array_map(function ($fk) {
                return $fk['column'];
            }, $meta['foreign_keys']);

            foreach ($meta['columns'] as $columnName => $columnMeta) {
                if (!preg_match('/_id$/', (string) $columnName)) {
                    continue;
                }

                if ($columnName === 'id' || in_array($columnName, $existingForeignKeyColumns, true)) {
                    continue;
                }

                $referencedTable = $this->inferReferencedTableFromColumn($columnName);
                if ($referencedTable === null || $referencedTable === $table) {
                    continue;
                }

                $this->tableMetadata[$table]['foreign_keys'][] = [
                    'column' => $columnName,
                    'referenced_table' => $referencedTable,
                    'referenced_column' => 'id',
                    'is_physical' => false,
                ];

                if (!in_array($referencedTable, $this->tableMetadata[$table]['dependencies'], true)) {
                    $this->tableMetadata[$table]['dependencies'][] = $referencedTable;
                }
            }
        }

        foreach ($this->tableMetadata as $table => $meta) {
            $naturalKeys = [];
            foreach ($this->naturalKeyPriority as $candidate) {
                if (isset($meta['columns'][$candidate])) {
                    $naturalKeys[] = $candidate;
                }
            }
            $this->tableMetadata[$table]['natural_keys'] = $naturalKeys;
        }

        $this->loaded = true;
    }

    /**
     * Carga metadatos usando queries compatibles con PostgreSQL.
     * Los dumps de origen pueden ser de MySQL (phpMyAdmin), pero la BD destino es PostgreSQL.
     */
    protected function loadMetadataPostgres()
    {
        // 1. Obtener todas las tablas del schema 'public'
        $tables = DB::select(
            "SELECT table_name
             FROM information_schema.tables
             WHERE table_schema = 'public' AND table_type = 'BASE TABLE'
             ORDER BY table_name"
        );

        foreach ($tables as $table) {
            $this->tableMetadata[$table->table_name] = [
                'columns' => [],
                'foreign_keys' => [],
                'dependencies' => [],
                'has_company_id' => false,
                'auto_increment_columns' => [],
                'primary_key' => null,
                'natural_keys' => [],
            ];
        }

        // 2. Obtener columnas con detección de PK y columnas seriales (auto-increment en PostgreSQL)
        // column_default LIKE 'nextval%' detecta columnas SERIAL/BIGSERIAL
        $columns = DB::select(
            "SELECT
                c.table_name,
                c.column_name,
                c.data_type,
                c.udt_name                                       AS column_type,
                c.is_nullable,
                c.column_default,
                c.ordinal_position,
                CASE WHEN pk.column_name IS NOT NULL THEN 'PRI' ELSE '' END AS column_key,
                CASE WHEN c.column_default LIKE 'nextval%' THEN 'auto_increment' ELSE '' END AS extra
             FROM information_schema.columns c
             LEFT JOIN (
                 SELECT kcu.table_name, kcu.column_name
                 FROM information_schema.table_constraints tc
                 JOIN information_schema.key_column_usage kcu
                     ON tc.constraint_name = kcu.constraint_name
                     AND tc.table_schema   = kcu.table_schema
                 WHERE tc.constraint_type = 'PRIMARY KEY'
                   AND tc.table_schema    = 'public'
             ) pk ON c.table_name = pk.table_name AND c.column_name = pk.column_name
             WHERE c.table_schema = 'public'
             ORDER BY c.table_name, c.ordinal_position"
        );

        foreach ($columns as $column) {
            if (!isset($this->tableMetadata[$column->table_name])) {
                continue;
            }

            $this->tableMetadata[$column->table_name]['columns'][$column->column_name] = [
                'data_type'   => $column->data_type,
                'column_type' => $column->column_type,
                'nullable'    => $column->is_nullable === 'YES',
                'default'     => $column->column_default,
                'column_key'  => $column->column_key,
                'extra'       => $column->extra,
            ];

            if ($column->column_name === 'company_id') {
                $this->tableMetadata[$column->table_name]['has_company_id'] = true;
            }

            if ($column->column_key === 'PRI' && $this->tableMetadata[$column->table_name]['primary_key'] === null) {
                $this->tableMetadata[$column->table_name]['primary_key'] = $column->column_name;
            }

            if ($column->extra === 'auto_increment') {
                $this->tableMetadata[$column->table_name]['auto_increment_columns'][] = $column->column_name;
            }
        }

        // 3. Obtener foreign keys reales (PostgreSQL usa referential_constraints + constraint_column_usage)
        $foreignKeys = DB::select(
            "SELECT
                kcu.table_name,
                kcu.column_name,
                ccu.table_name  AS referenced_table_name,
                ccu.column_name AS referenced_column_name
             FROM information_schema.table_constraints tc
             JOIN information_schema.key_column_usage kcu
                 ON tc.constraint_name = kcu.constraint_name
                 AND tc.table_schema   = kcu.table_schema
             JOIN information_schema.referential_constraints rc
                 ON tc.constraint_name = rc.constraint_name
                 AND tc.table_schema   = rc.constraint_schema
             JOIN information_schema.constraint_column_usage ccu
                 ON rc.unique_constraint_name   = ccu.constraint_name
                 AND rc.unique_constraint_schema = ccu.constraint_schema
             WHERE tc.constraint_type = 'FOREIGN KEY'
               AND tc.table_schema    = 'public'
             ORDER BY kcu.table_name, kcu.column_name"
        );

        foreach ($foreignKeys as $foreignKey) {
            if (!isset($this->tableMetadata[$foreignKey->table_name])) {
                continue;
            }

            $this->tableMetadata[$foreignKey->table_name]['foreign_keys'][] = [
                    'column'             => $foreignKey->column_name,
                    'referenced_table'   => $foreignKey->referenced_table_name,
                    'referenced_column'  => $foreignKey->referenced_column_name,
                    'is_physical'        => true,
            ];

            if (!in_array($foreignKey->referenced_table_name, $this->tableMetadata[$foreignKey->table_name]['dependencies'], true)) {
                $this->tableMetadata[$foreignKey->table_name]['dependencies'][] = $foreignKey->referenced_table_name;
            }
        }
    }

    /**
     * Carga metadatos usando queries compatibles con MySQL (fallback).
     */
    protected function loadMetadataMysql()
    {
        $database = config('database.connections.mysql.database');

        $tables = DB::select(
            "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME",
            [$database]
        );

        foreach ($tables as $table) {
            $this->tableMetadata[$table->TABLE_NAME] = [
                'columns' => [],
                'foreign_keys' => [],
                'dependencies' => [],
                'has_company_id' => false,
                'auto_increment_columns' => [],
                'primary_key' => null,
                'natural_keys' => [],
            ];
        }

        $columns = DB::select(
            'SELECT TABLE_NAME, COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_KEY, EXTRA, ORDINAL_POSITION
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ?
             ORDER BY TABLE_NAME, ORDINAL_POSITION',
            [$database]
        );

        foreach ($columns as $column) {
            if (!isset($this->tableMetadata[$column->TABLE_NAME])) {
                continue;
            }

            $this->tableMetadata[$column->TABLE_NAME]['columns'][$column->COLUMN_NAME] = [
                'data_type'   => $column->DATA_TYPE,
                'column_type' => $column->COLUMN_TYPE,
                'nullable'    => $column->IS_NULLABLE === 'YES',
                'default'     => $column->COLUMN_DEFAULT,
                'column_key'  => $column->COLUMN_KEY,
                'extra'       => $column->EXTRA,
            ];

            if ($column->COLUMN_NAME === 'company_id') {
                $this->tableMetadata[$column->TABLE_NAME]['has_company_id'] = true;
            }

            if ($column->COLUMN_KEY === 'PRI' && $this->tableMetadata[$column->TABLE_NAME]['primary_key'] === null) {
                $this->tableMetadata[$column->TABLE_NAME]['primary_key'] = $column->COLUMN_NAME;
            }

            if (strpos((string) $column->EXTRA, 'auto_increment') !== false) {
                $this->tableMetadata[$column->TABLE_NAME]['auto_increment_columns'][] = $column->COLUMN_NAME;
            }
        }

        $foreignKeys = DB::select(
            "SELECT TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ? AND REFERENCED_TABLE_NAME IS NOT NULL
             ORDER BY TABLE_NAME, COLUMN_NAME",
            [$database]
        );

        foreach ($foreignKeys as $foreignKey) {
            if (!isset($this->tableMetadata[$foreignKey->TABLE_NAME])) {
                continue;
            }

            $this->tableMetadata[$foreignKey->TABLE_NAME]['foreign_keys'][] = [
                'column'            => $foreignKey->COLUMN_NAME,
                'referenced_table'  => $foreignKey->REFERENCED_TABLE_NAME,
                'referenced_column' => $foreignKey->REFERENCED_COLUMN_NAME,
                'is_physical'       => true,
            ];

            if (!in_array($foreignKey->REFERENCED_TABLE_NAME, $this->tableMetadata[$foreignKey->TABLE_NAME]['dependencies'], true)) {
                $this->tableMetadata[$foreignKey->TABLE_NAME]['dependencies'][] = $foreignKey->REFERENCED_TABLE_NAME;
            }
        }
    }
}