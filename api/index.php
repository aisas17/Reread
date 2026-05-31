<?php

$root = dirname(__DIR__);
chdir($root);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = rawurldecode($path);
$path = trim($path, '/');

if ($path === '') {
    $path = 'index.php';
} elseif ($path === 'admin') {
    $path = 'admin/index.php';
} elseif (!str_ends_with($path, '.php')) {
    $path .= '.php';
}

$target = realpath($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path));

if (
    $target === false
    || !str_starts_with($target, $root)
    || !is_file($target)
    || pathinfo($target, PATHINFO_EXTENSION) !== 'php'
) {
    http_response_code(404);
    echo 'Not Found';
    exit;
}

$_SERVER['SCRIPT_FILENAME'] = $target;
$_SERVER['SCRIPT_NAME'] = '/' . str_replace(DIRECTORY_SEPARATOR, '/', substr($target, strlen($root) + 1));
$_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];

require $target;
