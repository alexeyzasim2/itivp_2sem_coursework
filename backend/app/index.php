<?php

$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$queryString = $_SERVER['QUERY_STRING'] ?? '';

$handlerPath = null;

if (preg_match('#/backend/api/(auth|auth\.php)#', $path) || strpos($queryString, 'action=') !== false) {
    if (strpos($queryString, 'action=register') !== false || strpos($path, '/register') !== false) {
        $handlerPath = __DIR__ . '/../api/auth.php';
    } elseif (strpos($queryString, 'action=login') !== false || strpos($path, '/login') !== false) {
        $handlerPath = __DIR__ . '/../api/auth.php';
    } elseif (strpos($queryString, 'action=logout') !== false || strpos($path, '/logout') !== false) {
        $handlerPath = __DIR__ . '/../api/auth.php';
    } elseif (strpos($queryString, 'action=check') !== false) {
        $handlerPath = __DIR__ . '/../api/auth.php';
    } elseif (strpos($path, '/backend/api/auth') !== false) {
        $handlerPath = __DIR__ . '/../api/auth.php';
    }
}

if (!$handlerPath) {
    if (strpos($path, '/backend/api/dreams') !== false || strpos($path, '/backend/api/dreams.php') !== false) {
        $handlerPath = __DIR__ . '/../api/dreams.php';
    } elseif (strpos($path, '/backend/api/analyze') !== false || strpos($path, '/backend/api/analyze.php') !== false) {
        $handlerPath = __DIR__ . '/../api/analyze.php';
    } elseif (strpos($path, '/backend/api/stats') !== false || strpos($path, '/backend/api/stats.php') !== false) {
        $handlerPath = __DIR__ . '/../api/stats.php';
    } elseif (strpos($path, '/backend/api/admin') !== false || strpos($path, '/backend/api/admin.php') !== false) {
        $handlerPath = __DIR__ . '/../api/admin.php';
    }
}

if (!$handlerPath) {
    http_response_code(404);
    echo json_encode(['error' => 'Not found', 'path' => $path, 'query' => $queryString]);
    exit;
}

if ($handlerPath && file_exists($handlerPath)) {
    require_once $handlerPath;
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Handler not found']);
    exit;
}

