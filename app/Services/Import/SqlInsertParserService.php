<?php

namespace App\Services\Import;

class SqlInsertParserService
{
    protected $schemaService;

    public function __construct(ImportSchemaService $schemaService)
    {
        $this->schemaService = $schemaService;
    }

    public function parseFile($absolutePath)
    {
        // Usa streaming para archivos grandes para evitar agotamiento de memoria
        return $this->parseFileStreaming($absolutePath);
    }

    public function parseFileSummary($absolutePath, $chunkSize = 2097152)
    {
        $metadata = $this->schemaService->getTableMetadata();
        $tables = [];
        $issues = [];
        $statementCount = 0;

        try {
            foreach ($this->iterateInsertStatements($absolutePath) as $statement) {
                $parsed = $this->parseInsertStatementSummary($statement, $metadata);
                if ($parsed !== null && !isset($parsed['issue'])) {
                    $table = $parsed['table'];
                    if (!isset($tables[$table])) {
                        $tables[$table] = [
                            'table' => $table,
                            'columns' => $parsed['columns'],
                            'rows' => [],
                            'row_count' => 0,
                            'ignore' => $parsed['ignore'],
                        ];
                    }

                    $tables[$table]['row_count'] += $parsed['row_count'];
                    $statementCount++;
                } elseif (isset($parsed['issue'])) {
                    $issues[] = $parsed['issue'];
                }
            }
        } catch (\RuntimeException $exception) {
            return ['tables' => [], 'issues' => [$exception->getMessage()], 'statement_count' => 0];
        }

        return [
            'tables' => $tables,
            'issues' => $issues,
            'statement_count' => $statementCount,
        ];
    }

    public function parseFileStreaming($absolutePath, $chunkSize = 2097152)
    {
        $metadata = $this->schemaService->getTableMetadata();
        $tables = [];
        $issues = [];
        $statementCount = 0;
        try {
            foreach ($this->iterateInsertStatements($absolutePath) as $statement) {
                $parsed = $this->parseInsertStatement($statement, $metadata);
                if ($parsed !== null && !isset($parsed['issue'])) {
                    $table = $parsed['table'];
                    if (!isset($tables[$table])) {
                        $tables[$table] = [
                            'table' => $table,
                            'columns' => $parsed['columns'],
                            'rows' => [],
                            'row_count' => 0,
                            'ignore' => $parsed['ignore'],
                        ];
                    }

                    $tables[$table]['rows'] = array_merge($tables[$table]['rows'], $parsed['rows']);
                    $tables[$table]['row_count'] = count($tables[$table]['rows']);
                    $statementCount++;
                } elseif (isset($parsed['issue'])) {
                    $issues[] = $parsed['issue'];
                }

                if (memory_get_usage(true) > 268435456) {
                    gc_collect_cycles();
                }
            }
        } catch (\RuntimeException $exception) {
            return ['tables' => [], 'issues' => [$exception->getMessage()], 'statement_count' => 0];
        }

        return [
            'tables' => $tables,
            'issues' => $issues,
            'statement_count' => $statementCount,
        ];
    }

    protected function findStatementEnd($buffer)
    {
        $length = strlen($buffer);
        $inSingleQuote = false;
        $inDoubleQuote = false;

        for ($i = 0; $i < $length; $i++) {
            $char = $buffer[$i];
            $prev = $i > 0 ? $buffer[$i - 1] : '';

            // Controla el estado de comillas
            if ($char === "'" && !$inDoubleQuote && $prev !== '\\') {
                $inSingleQuote = !$inSingleQuote;
            } elseif ($char === '"' && !$inSingleQuote && $prev !== '\\') {
                $inDoubleQuote = !$inDoubleQuote;
            }

            // Detecta fin de sentencia
            if ($char === ';' && !$inSingleQuote && !$inDoubleQuote) {
                return $i;
            }
        }

        return false;
    }

    protected function iterateInsertStatements($absolutePath)
    {
        $handle = fopen($absolutePath, 'r');
        if (!$handle) {
            throw new \RuntimeException('No se pudo abrir el archivo: ' . $absolutePath);
        }

        try {
            $capturing = false;
            $buffer = '';

            while (($line = fgets($handle)) !== false) {
                if (!$capturing) {
                    if (!preg_match('/^\s*INSERT\s+(IGNORE\s+)?INTO\s+/i', $line)) {
                        continue;
                    }

                    $capturing = true;
                    $buffer = $line;
                } else {
                    $buffer .= $line;
                }

                if (preg_match('/;\s*$/', trim($line))) {
                    yield $buffer;
                    $capturing = false;
                    $buffer = '';
                }
            }

            if ($capturing && trim($buffer) !== '') {
                yield $buffer;
            }
        } finally {
            fclose($handle);
        }
    }

    public function parseSql($sql)
    {
        $sql = preg_replace('/^\xEF\xBB\xBF/', '', (string) $sql);
        $statements = $this->splitSqlStatements($sql);
        $metadata = $this->schemaService->getTableMetadata();

        $tables = [];
        $issues = [];

        foreach ($statements as $statement) {
            $parsed = $this->parseInsertStatement($statement, $metadata);
            if ($parsed === null) {
                continue;
            }

            if (isset($parsed['issue'])) {
                $issues[] = $parsed['issue'];
                continue;
            }

            $table = $parsed['table'];
            if (!isset($tables[$table])) {
                $tables[$table] = [
                    'table' => $table,
                    'columns' => $parsed['columns'],
                    'rows' => [],
                    'row_count' => 0,
                    'ignore' => $parsed['ignore'],
                ];
            }

            $tables[$table]['rows'] = array_merge($tables[$table]['rows'], $parsed['rows']);
            $tables[$table]['row_count'] = count($tables[$table]['rows']);
        }

        return [
            'tables' => $tables,
            'issues' => $issues,
            'statement_count' => count($statements),
        ];
    }

    protected function parseInsertStatement($sql, array $metadata = [])
    {
        $trimmed = trim($sql);
        if ($trimmed === '') {
            return null;
        }

        if (!preg_match('/^INSERT\s+(IGNORE\s+)?INTO\s+(`?[a-zA-Z0-9_]+`?(?:\.`?[a-zA-Z0-9_]+`?)?)\s*(?:\((.*?)\))?\s*VALUES\s*(.+)$/is', $trimmed, $matches)) {
            return null;
        }

        $tableRaw = $matches[2];
        $tableParts = explode('.', str_replace('`', '', $tableRaw));
        $table = end($tableParts);
        $columnSection = isset($matches[3]) ? trim((string) $matches[3]) : '';
        $valuesSection = trim($matches[4]);

        $columns = [];
        if ($columnSection !== '') {
            $columns = array_map(function ($column) {
                return trim(str_replace('`', '', $column));
            }, explode(',', $columnSection));
        } elseif (isset($metadata[$table])) {
            $columns = array_keys($metadata[$table]['columns']);
        }

        if (empty($columns)) {
            return ['issue' => 'No se pudieron resolver columnas para la tabla ' . $table . '.'];
        }

        $rows = [];
        $tuples = $this->extractValueTuples($valuesSection);
        foreach ($tuples as $tuple) {
            $values = $this->splitCsvSql($tuple);
            if (count($values) !== count($columns)) {
                return ['issue' => 'Cantidad de columnas/valores inconsistente en la tabla ' . $table . '.'];
            }

            $row = [];
            foreach ($columns as $index => $columnName) {
                $row[$columnName] = $this->decodeSqlValue($values[$index]);
            }

            $rows[] = $row;
        }

        return [
            'table' => $table,
            'columns' => $columns,
            'rows' => $rows,
            'ignore' => !empty($matches[1]),
        ];
    }

    protected function parseInsertStatementSummary($sql, array $metadata = [])
    {
        $trimmed = trim($sql);
        if ($trimmed === '') {
            return null;
        }

        if (!preg_match('/^INSERT\s+(IGNORE\s+)?INTO\s+(`?[a-zA-Z0-9_]+`?(?:\.`?[a-zA-Z0-9_]+`?)?)\s*(?:\((.*?)\))?\s*VALUES\s*(.+)$/is', $trimmed, $matches)) {
            return null;
        }

        $tableRaw = $matches[2];
        $tableParts = explode('.', str_replace('`', '', $tableRaw));
        $table = end($tableParts);
        $columnSection = isset($matches[3]) ? trim((string) $matches[3]) : '';
        $valuesSection = trim($matches[4]);

        $columns = [];
        if ($columnSection !== '') {
            $columns = array_map(function ($column) {
                return trim(str_replace('`', '', $column));
            }, explode(',', $columnSection));
        } elseif (isset($metadata[$table])) {
            $columns = array_keys($metadata[$table]['columns']);
        }

        if (empty($columns)) {
            return ['issue' => 'No se pudieron resolver columnas para la tabla ' . $table . '.'];
        }

        return [
            'table' => $table,
            'columns' => $columns,
            'rows' => [],
            'row_count' => $this->countValueTuples($valuesSection),
            'ignore' => !empty($matches[1]),
        ];
    }

    protected function countValueTuples($valuesSection)
    {
        $count = 0;
        $depth = 0;
        $inSingle = false;
        $inDouble = false;
        $length = strlen($valuesSection);

        for ($i = 0; $i < $length; $i++) {
            $char = $valuesSection[$i];
            $prev = $i > 0 ? $valuesSection[$i - 1] : '';

            if ($char === "'" && !$inDouble && $prev !== '\\') {
                $inSingle = !$inSingle;
            } elseif ($char === '"' && !$inSingle && $prev !== '\\') {
                $inDouble = !$inDouble;
            }

            if ($inSingle || $inDouble) {
                continue;
            }

            if ($char === '(') {
                $depth++;
                continue;
            }

            if ($char === ')') {
                if ($depth > 0) {
                    $depth--;
                    if ($depth === 0) {
                        $count++;
                    }
                }
            }
        }

        return $count;
    }

    protected function decodeSqlValue($value)
    {
        $value = trim((string) $value);

        if (strtoupper($value) === 'NULL') {
            return null;
        }

        if ((substr($value, 0, 1) === "'" && substr($value, -1) === "'") || (substr($value, 0, 1) === '"' && substr($value, -1) === '"')) {
            $quote = substr($value, 0, 1);
            $value = substr($value, 1, -1);
            if ($quote === "'") {
                $value = str_replace(["\\'", "''", '\\\\'], ["'", "'", '\\'], $value);
            } else {
                $value = str_replace(['\\"', '\\\\'], ['"', '\\'], $value);
            }
            return $value;
        }

        if (is_numeric($value)) {
            return strpos($value, '.') !== false ? (float) $value : (int) $value;
        }

        return $value;
    }

    public function splitSqlStatements($sql)
    {
        $statements = [];
        $buffer = '';
        $inSingle = false;
        $inDouble = false;
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $prev = $i > 0 ? $sql[$i - 1] : '';

            if ($char === "'" && !$inDouble && $prev !== '\\') {
                $inSingle = !$inSingle;
            } elseif ($char === '"' && !$inSingle && $prev !== '\\') {
                $inDouble = !$inDouble;
            }

            if ($char === ';' && !$inSingle && !$inDouble) {
                $statements[] = $buffer;
                $buffer = '';
                continue;
            }

            $buffer .= $char;
        }

        if (trim($buffer) !== '') {
            $statements[] = $buffer;
        }

        return $statements;
    }

    public function extractValueTuples($valuesSection)
    {
        $tuples = [];
        $buffer = '';
        $depth = 0;
        $inSingle = false;
        $inDouble = false;
        $length = strlen($valuesSection);

        for ($i = 0; $i < $length; $i++) {
            $char = $valuesSection[$i];
            $prev = $i > 0 ? $valuesSection[$i - 1] : '';

            if ($char === "'" && !$inDouble && $prev !== '\\') {
                $inSingle = !$inSingle;
            } elseif ($char === '"' && !$inSingle && $prev !== '\\') {
                $inDouble = !$inDouble;
            }

            if (!$inSingle && !$inDouble) {
                if ($char === '(') {
                    if ($depth === 0) {
                        $buffer = '';
                    }
                    $depth++;
                    continue;
                }

                if ($char === ')') {
                    $depth--;
                    if ($depth === 0) {
                        $tuples[] = $buffer;
                        $buffer = '';
                        continue;
                    }
                }
            }

            if ($depth > 0) {
                $buffer .= $char;
            }
        }

        return $tuples;
    }

    public function splitCsvSql($tuple)
    {
        $parts = [];
        $buffer = '';
        $inSingle = false;
        $inDouble = false;
        $length = strlen($tuple);

        for ($i = 0; $i < $length; $i++) {
            $char = $tuple[$i];
            $prev = $i > 0 ? $tuple[$i - 1] : '';

            if ($char === "'" && !$inDouble && $prev !== '\\') {
                $inSingle = !$inSingle;
            } elseif ($char === '"' && !$inSingle && $prev !== '\\') {
                $inDouble = !$inDouble;
            }

            if ($char === ',' && !$inSingle && !$inDouble) {
                $parts[] = trim($buffer);
                $buffer = '';
                continue;
            }

            $buffer .= $char;
        }

        if ($buffer !== '') {
            $parts[] = trim($buffer);
        }

        return $parts;
    }
}