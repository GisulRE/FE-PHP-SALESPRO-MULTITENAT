<?php

$install = is_dir('install');
ini_set('max_execution_time', 0);
ini_set('max_input_time', -1);
ini_set('max_input_vars', 10000);
ini_set('memory_limit','-1');
if ($install == true) {
	header("location:install/index.php");
}
else {
	$uri = urldecode(
	    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
	);

	if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri)) {
	    return false;
	}

	require_once __DIR__.'/public/index.php';
}
