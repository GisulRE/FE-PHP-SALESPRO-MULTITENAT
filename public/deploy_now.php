<?php
// Script de despliegue temporal - ELIMINAR DESPUES DE USAR
// Clave de seguridad para evitar uso no autorizado
define('SECRET_KEY', 'gisul2024deploy');

if (!isset($_GET['key']) || $_GET['key'] !== SECRET_KEY) {
    http_response_code(403);
    die('Acceso denegado.');
}

header('Content-Type: text/plain; charset=utf-8');
$projectPath = '/home/admingisulsrl/public_html/pos_multitenant';

echo "=== DESPLIEGUE AUTOMATICO ===\n\n";

$commands = [
    "cd {$projectPath} && git fetch origin 2>&1",
    "cd {$projectPath} && git reset --hard origin/main 2>&1",
    "cd {$projectPath} && rm -rf storage/framework/views/* 2>&1",
    "cd {$projectPath} && rm -rf storage/framework/cache/data/* 2>&1",
    "cd {$projectPath} && php artisan view:clear 2>&1",
    "cd {$projectPath} && php artisan config:clear 2>&1",
    "cd {$projectPath} && php artisan route:clear 2>&1",
    "cd {$projectPath} && php artisan cache:clear 2>&1",
];

foreach ($commands as $cmd) {
    echo ">> {$cmd}\n";
    $output = shell_exec($cmd);
    echo $output . "\n";
}

echo "\n=== COMPLETADO ===\n";
echo "Eliminando este script por seguridad...\n";
unlink(__FILE__);
echo "Script eliminado. Ya puedes recargar el sitio.\n";