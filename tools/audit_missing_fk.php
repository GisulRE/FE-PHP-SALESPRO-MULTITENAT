<?php

$root = dirname(__DIR__);
$migrationDir = $root . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations';

$files = glob($migrationDir . DIRECTORY_SEPARATOR . '*.php');
sort($files);

$results = [];

foreach ($files as $file) {
    $content = file_get_contents($file);
    if ($content === false) {
        continue;
    }

    if (!preg_match_all("/Schema::create\(\s*'([^']+)'\s*,\s*function\s*\(Blueprint\s*\$table\)\s*\{([\s\S]*?)\}\s*\);/m", $content, $matches, PREG_SET_ORDER)) {
        continue;
    }

    foreach ($matches as $match) {
        $tableName = $match[1];
        $block = $match[2];

        $idColumns = [];
        if (preg_match_all("/\$table->(?:unsignedBigInteger|unsignedInteger|bigInteger|integer|foreignId)\(\s*'([^']+_id)'\s*\)/", $block, $idMatches)) {
            foreach ($idMatches[1] as $col) {
                $idColumns[$col] = true;
            }
        }

        $fkColumns = [];
        if (preg_match_all("/\$table->foreign\(\s*'([^']+)'\s*\)/", $block, $fkMatches)) {
            foreach ($fkMatches[1] as $col) {
                $fkColumns[$col] = true;
            }
        }

        // foreignId('x')->constrained() style without explicit foreign('x')
        if (preg_match_all("/\$table->foreignId\(\s*'([^']+)'\s*\)\s*->[^;]*constrained\s*\(/", $block, $constrainedMatches)) {
            foreach ($constrainedMatches[1] as $col) {
                $fkColumns[$col] = true;
            }
        }

        foreach (array_keys($idColumns) as $col) {
            if ($col === 'id') {
                continue;
            }

            if (!isset($fkColumns[$col])) {
                $results[] = [
                    'file' => str_replace($root . DIRECTORY_SEPARATOR, '', $file),
                    'table' => $tableName,
                    'column' => $col,
                ];
            }
        }
    }
}

if (empty($results)) {
    echo "NO_MISSING_FKS\n";
    exit(0);
}

foreach ($results as $row) {
    echo $row['table'] . '|' . $row['column'] . '|' . $row['file'] . PHP_EOL;
}
