<?php
// router.php — pequeño router para el servidor embebido de PHP
// Colocar este archivo en la raíz del proyecto y ejecutar:
// php -S 127.0.0.1:8000 router.php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Ruta al archivo solicitado en el sistema de archivos (relativa a la raíz del repo)
$file = __DIR__ . $uri;

// Si el archivo existe (assets, css, js, imágenes), dejar que el servidor embebido lo sirva
if ($uri !== '/' && file_exists($file) && is_file($file)) {
    return false; // Dejar que el servidor sirva el archivo estático
}

// En caso contrario, delegar en el front controller de Laravel (public/index.php)
require_once __DIR__ . '/public/index.php';
