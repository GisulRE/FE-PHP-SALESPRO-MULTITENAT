<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$rows = [];
foreach (glob(app_path('*.php')) as $file) {
    $content = file_get_contents($file);
    if (strpos($content, 'extends Model') === false) {
        continue;
    }

    if (!preg_match('/class\s+([A-Za-z0-9_]+)\s+extends\s+Model/', $content, $m)) {
        continue;
    }

    $class = $m[1];
    $fqcn = 'App\\' . $class;
    if (!class_exists($fqcn)) {
        continue;
    }

    try {
        $model = new $fqcn();
        $rows[] = [
            'table' => $model->getTable(),
            'model' => $class,
            'file' => basename($file),
        ];
    } catch (Throwable $e) {
        // Skip non-instantiable models.
    }
}

usort($rows, function ($a, $b) {
    return [$a['table'], $a['model']] <=> [$b['table'], $b['model']];
});

foreach ($rows as $row) {
    echo $row['table'] . '|' . $row['model'] . '|' . $row['file'] . PHP_EOL;
}
