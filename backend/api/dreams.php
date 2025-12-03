<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../repository/config/session.php';
$pdo = require_once __DIR__ . '/../repository/config/db.php';
require_once __DIR__ . '/../service/DreamService.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

requireAuth();

$input = json_decode(file_get_contents('php://input'), true);

$requestUri = $_SERVER['REQUEST_URI'];
$dreamId = null;

$path = parse_url($requestUri, PHP_URL_PATH);

if (preg_match('/\/dreams(?:\.php)?\/(\d+)/', $path, $matches)) {
    $dreamId = (int)$matches[1];
}

$dreamService = new DreamService($pdo);
$userId = getCurrentUserId();

try {
    switch ($method) {
        case 'GET':
            if ($dreamId) {
                $dream = $dreamService->getDreamById($dreamId, $userId);
                echo json_encode([
                    'success' => true,
                    'dream' => $dream
                ]);
            } else {
                $dreams = $dreamService->getDreamsByUserId($userId);
                echo json_encode([
                    'success' => true,
                    'dreams' => $dreams
                ]);
            }
            break;
            
        case 'POST':
            $dream = $dreamService->createDream($userId, $input);
            echo json_encode([
                'success' => true,
                'message' => 'Dream created successfully',
                'dream' => $dream
            ]);
            break;
            
        case 'PUT':
            if (!$dreamId) {
                throw new Exception('Dream ID is required');
            }
            
            $dream = $dreamService->updateDream($dreamId, $userId, $input);
            echo json_encode([
                'success' => true,
                'message' => 'Dream updated successfully',
                'dream' => $dream
            ]);
            break;
            
        case 'DELETE':
            if (!$dreamId) {
                throw new Exception('Dream ID is required');
            }
            
            $dreamService->deleteDream($dreamId, $userId);
            echo json_encode([
                'success' => true,
                'message' => 'Dream deleted successfully'
            ]);
            break;
            
        default:
            throw new Exception('Method not allowed');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
