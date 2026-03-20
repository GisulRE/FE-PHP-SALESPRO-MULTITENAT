<?php

namespace App\Services\Import;

use Illuminate\Support\Facades\File;

class DumpSplitService
{
    public function split($sourcePath, $outputPath, $includeEmpty = true)
    {
        if (!is_file($sourcePath)) {
            throw new \RuntimeException('No se encontro el archivo de origen: ' . $sourcePath);
        }

        File::ensureDirectoryExists($outputPath);

        $tables = [];
        $tableOrder = [];

        $handle = fopen($sourcePath, 'r');
        if (!$handle) {
            throw new \RuntimeException('No se pudo abrir el archivo: ' . $sourcePath);
        }

        $captureType = null; // create|insert|null
        $currentTable = null;
        $buffer = '';

        try {
            while (($line = fgets($handle)) !== false) {
                if ($captureType === null) {
                    if (preg_match('/^\s*CREATE\s+TABLE\s+`?([a-zA-Z0-9_]+)`?/i', $line, $m)) {
                        $captureType = 'create';
                        $currentTable = $m[1];
                        $buffer = $line;
                    } elseif (preg_match('/^\s*INSERT\s+(?:IGNORE\s+)?INTO\s+`?([a-zA-Z0-9_]+)`?/i', $line, $m)) {
                        $captureType = 'insert';
                        $currentTable = $m[1];
                        $buffer = $line;
                    } else {
                        continue;
                    }
                } else {
                    $buffer .= $line;
                }

                if (!$this->statementEnds($line)) {
                    continue;
                }

                if (!isset($tables[$currentTable])) {
                    $tables[$currentTable] = [
                        'table' => $currentTable,
                        'create' => [],
                        'inserts' => [],
                        'row_count' => 0,
                    ];
                    $tableOrder[] = $currentTable;
                }

                if ($captureType === 'create') {
                    $tables[$currentTable]['create'][] = trim($buffer);
                } else {
                    $tables[$currentTable]['inserts'][] = trim($buffer);
                    $tables[$currentTable]['row_count'] += $this->countInsertRows($buffer);
                }

                $captureType = null;
                $currentTable = null;
                $buffer = '';
            }
        } finally {
            fclose($handle);
        }

        $generated = 0;
        $summaryTables = [];

        foreach ($tableOrder as $tableName) {
            $payload = $tables[$tableName];
            $hasRows = count($payload['inserts']) > 0;
            if (!$hasRows && !$includeEmpty) {
                continue;
            }

            $generated++;
            $targetFile = $outputPath . DIRECTORY_SEPARATOR . sprintf('%03d_%s.sql', $generated, $tableName);

            $chunks = [];
            $chunks[] = '-- Split dump table: ' . $tableName;
            $chunks[] = '-- Rows detected: ' . $payload['row_count'];
            $chunks[] = 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";';
            $chunks[] = 'SET time_zone = "+00:00";';
            $chunks[] = '';

            if (!empty($payload['create'])) {
                $chunks[] = '-- Table structure';
                foreach ($payload['create'] as $createSql) {
                    $chunks[] = $createSql;
                    $chunks[] = '';
                }
            }

            if (!empty($payload['inserts'])) {
                $chunks[] = '-- Table rows';
                foreach ($payload['inserts'] as $insertSql) {
                    $chunks[] = $insertSql;
                    $chunks[] = '';
                }
            } else {
                $chunks[] = '-- No INSERT rows for this table.';
                $chunks[] = '';
            }

            file_put_contents($targetFile, implode(PHP_EOL, $chunks));

            $summaryTables[] = [
                'table' => $tableName,
                'rows' => $payload['row_count'],
                'file' => basename($targetFile),
                'path' => $targetFile,
            ];
        }

        $summary = [
            'source' => $sourcePath,
            'generated_files' => $generated,
            'tables_detected' => count($tableOrder),
            'include_empty' => (bool) $includeEmpty,
            'tables' => $summaryTables,
        ];

        $summaryPath = $outputPath . DIRECTORY_SEPARATOR . '_summary.json';
        file_put_contents($summaryPath, json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $summary['summary_path'] = $summaryPath;

        return $summary;
    }

    protected function statementEnds($line)
    {
        return preg_match('/;\s*$/', rtrim($line)) === 1;
    }

    protected function countInsertRows($sql)
    {
        if (!preg_match('/\bVALUES\b\s*(.+)$/is', $sql, $m)) {
            return 0;
        }

        $values = $m[1];
        $len = strlen($values);
        $depth = 0;
        $inSingle = false;
        $inDouble = false;
        $count = 0;

        for ($i = 0; $i < $len; $i++) {
            $char = $values[$i];
            $prev = $i > 0 ? $values[$i - 1] : '';

            if ($char === "'" && !$inDouble && $prev !== '\\') {
                $inSingle = !$inSingle;
                continue;
            }

            if ($char === '"' && !$inSingle && $prev !== '\\') {
                $inDouble = !$inDouble;
                continue;
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
}
