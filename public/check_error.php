<?php
header('Content-Type: text/plain; charset=utf-8');

echo "=== PHP INFO ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Memory Limit: " . ini_get('memory_limit') . "\n\n";

echo "=== LARAVEL LOG (Ultimas 40 lineas) ===\n";
$logPath = dirname(__DIR__) . '/storage/logs/laravel.log';
if (file_exists($logPath)) {
    $lines = file($logPath);
    $lastLines = array_slice($lines, -40);
    echo implode("", $lastLines);
} else {
    echo "No existe storage/logs/laravel.log\n";
}

echo "\n\n=== VERIFICANDO PERMISSION STORE ===\n";
try {
    require dirname(__DIR__) . '/vendor/autoload.php';
    $app = require_once dirname(__DIR__) . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );
    echo "Laravel Boot OK!\n";
    echo "Permission cache store: " . config('permission.cache.store') . "\n";
} catch (\Throwable $e) {
    echo "ERROR AL CARGAR LARAVEL:\n";
    echo "Clase: " . get_class($e) . "\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}