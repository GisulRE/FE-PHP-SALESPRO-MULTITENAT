<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$database = config('database.connections.mysql.database');

$sql = <<<SQL
SELECT c.TABLE_NAME, c.COLUMN_NAME
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
ORDER BY c.TABLE_NAME, c.COLUMN_NAME
SQL;

$rows = Illuminate\Support\Facades\DB::select($sql, [$database]);

if (empty($rows)) {
    echo "NO_MISSING_FKS_IN_DB\n";
    exit(0);
}

foreach ($rows as $row) {
    echo $row->TABLE_NAME . '|' . $row->COLUMN_NAME . PHP_EOL;
}
