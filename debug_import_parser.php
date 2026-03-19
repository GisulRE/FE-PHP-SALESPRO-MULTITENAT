<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$parser = $app->make(App\Services\Import\SqlInsertParserService::class);

$fullPath = storage_path('app/restore_preview/hU2awewgOmGMywO7tyoLqGy0P7FEcZ1qBK3zHipc.txt');
$headPath = storage_path('app/preview_head_test.sql');

$results = [
    'full' => [
        'exists' => file_exists($fullPath),
        'summary' => $parser->parseFileSummary($fullPath),
    ],
    'head' => [
        'exists' => file_exists($headPath),
        'summary' => file_exists($headPath) ? $parser->parseFileSummary($headPath) : null,
    ],
];

echo json_encode([
    'full_count' => count($results['full']['summary']['tables']),
    'full_tables' => array_keys($results['full']['summary']['tables']),
    'full_issues_count' => count($results['full']['summary']['issues']),
    'full_issues_sample' => array_slice($results['full']['summary']['issues'], 0, 20),
    'head_count' => $results['head']['summary'] ? count($results['head']['summary']['tables']) : null,
    'head_tables' => $results['head']['summary'] ? array_keys($results['head']['summary']['tables']) : null,
    'head_issues_count' => $results['head']['summary'] ? count($results['head']['summary']['issues']) : null,
    'head_issues_sample' => $results['head']['summary'] ? array_slice($results['head']['summary']['issues'], 0, 20) : null,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);