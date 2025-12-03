<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../repository/config/session.php';
$pdo = require_once __DIR__ . '/../repository/config/db.php';
require_once __DIR__ . '/../service/AdminService.php';
require_once __DIR__ . '/../service/DreamService.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

requireAdmin();

$input = json_decode(file_get_contents('php://input'), true);

$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

$action = null;
$userId = null;
$dreamId = null;

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    if (isset($_GET['id'])) {
        if ($action === 'user') {
            $userId = (int)$_GET['id'];
        } elseif ($action === 'dream') {
            $dreamId = (int)$_GET['id'];
        }
    }
} elseif (preg_match('/\/admin\/users\/(\d+)/', $path, $matches)) {
    $userId = (int)$matches[1];
    $action = 'user';
} elseif (preg_match('/\/admin\/dreams\/(\d+)/', $path, $matches)) {
    $dreamId = (int)$matches[1];
    $action = 'dream';
} elseif (strpos($path, '/admin/users') !== false || isset($_GET['users'])) {
    $action = 'users';
} elseif (strpos($path, '/admin/dreams') !== false || isset($_GET['dreams'])) {
    $action = 'dreams';
}

$adminService = new AdminService($pdo);
$currentUserId = getCurrentUserId();

try {
    switch ($method) {
        case 'GET':
            if ($action === 'users') {
                $users = $adminService->getAllUsers();
                echo json_encode([
                    'success' => true,
                    'users' => $users
                ]);
            } elseif ($action === 'user' && $userId) {
                $user = $adminService->getUserById($userId);
                echo json_encode([
                    'success' => true,
                    'user' => $user
                ]);
            } elseif ($action === 'dreams') {
                $dreams = $adminService->getAllDreams();
                echo json_encode([
                    'success' => true,
                    'dreams' => $dreams
                ]);
            } elseif ($action === 'dream' && $dreamId) {
                $dreamService = new DreamService($pdo);
                $dream = $dreamService->getDreamById($dreamId, null);
                echo json_encode([
                    'success' => true,
                    'dream' => $dream
                ]);
            } else {
                throw new Exception('Invalid action');
            }
            break;
            
        case 'PUT':
            if ($action === 'user' && $userId) {
                $user = $adminService->updateUser($userId, $input);
                echo json_encode([
                    'success' => true,
                    'message' => 'User updated successfully',
                    'user' => $user
                ]);
            } else {
                throw new Exception('Invalid action');
            }
            break;
            
        case 'DELETE':
            if ($action === 'dream' && $dreamId) {
                $adminService->deleteDream($dreamId);
                echo json_encode([
                    'success' => true,
                    'message' => 'Dream deleted successfully'
                ]);
            } elseif ($action === 'user' && $userId) {
                $adminService->deleteUser($userId, $currentUserId);
                echo json_encode([
                    'success' => true,
                    'message' => 'User deleted successfully'
                ]);
            } else {
                throw new Exception('Invalid action');
            }
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
