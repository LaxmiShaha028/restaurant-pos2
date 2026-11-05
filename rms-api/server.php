<?php

$publicPath = __DIR__.'/public';
$requestPath = rawurldecode(
    parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'
);

if ($requestPath !== '/' && is_file($publicPath.$requestPath)) {
    return false;
}

$isBackendRoute =
    $requestPath === '/api'
    || str_starts_with($requestPath, '/api/')
    || $requestPath === '/up';

if (! $isBackendRoute) {
    if (! in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
        http_response_code(404);
        exit;
    }

    $indexPath = $publicPath.'/index.html';

    if (! is_file($indexPath)) {
        http_response_code(503);
        header('Content-Type: text/plain; charset=UTF-8');
        echo 'Frontend bundle is not available.';
        exit;
    }

    header('Content-Type: text/html; charset=UTF-8');

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'HEAD') {
        readfile($indexPath);
    }

    exit;
}

require $publicPath.'/index.php';
