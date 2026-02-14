<?php

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// Some views in this project reference assets with a "public/" prefix
// (e.g. asset('public/css/app.css')). When running via `php artisan serve`,
// the document root is already `public/`, so we normalize that here.
if (strpos($uri, '/public/') === 0) {
    $uri = substr($uri, 7);
}

// This file allows us to emulate Apache's "mod_rewrite" functionality from the
// built-in PHP web server. This provides a convenient way to test a Laravel
// application without having installed a "real" web server software here.
if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri)) {
    return false;
}

require_once __DIR__.'/public/index.php';
