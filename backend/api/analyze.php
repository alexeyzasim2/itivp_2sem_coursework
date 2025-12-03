<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../repository/config/session.php';
$pdo = require_once __DIR__ . '/../repository/config/db.php';
require_once __DIR__ . '/../service/AnalyzeService.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

requireAuth();

$analyzeService = new AnalyzeService($pdo);
$userId = getCurrentUserId();

try {
    if ($method !== 'GET' && $method !== 'POST') {
        throw new Exception('Method not allowed');
    }
    
    $dreamId = null;
    $dreamContent = null;
    
    if ($method === 'GET') {
        $requestUri = $_SERVER['REQUEST_URI'];
        $path = parse_url($requestUri, PHP_URL_PATH);
        
        if (isset($_GET['id'])) {
            $dreamId = (int)$_GET['id'];
        } elseif (preg_match('/\/analyze(?:\.php)?\/(\d+)/', $path, $matches)) {
            $dreamId = (int)$matches[1];
        } elseif (isset($_GET['dream_id'])) {
            $dreamId = (int)$_GET['dream_id'];
        } else {
            $dreamId = null;
        }
        
        if (!$dreamId) {
            throw new Exception('Dream ID is required');
        }
    } else {
        $input = json_decode(file_get_contents('php://input'), true);
        $dreamId = $input['dream_id'] ?? null;
        $dreamContent = $input['content'] ?? null;
        
        if (!$dreamContent && !$dreamId) {
            throw new Exception('Dream content or ID is required');
        }
    }
    
    $symbols = $analyzeService->analyzeDream($dreamId, $userId, $dreamContent);
    
    echo json_encode([
        'success' => true,
        'symbols' => $symbols,
        'count' => count($symbols)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
