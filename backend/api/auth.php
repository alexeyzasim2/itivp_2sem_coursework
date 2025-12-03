<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../repository/config/session.php';
$pdo = require_once __DIR__ . '/../repository/config/db.php';
require_once __DIR__ . '/../service/AuthService.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$requestUri = $_SERVER['REQUEST_URI'];
$action = null;

if (strpos($requestUri, '/register') !== false) {
    $action = 'register';
} elseif (strpos($requestUri, '/login') !== false) {
    $action = 'login';
} elseif (strpos($requestUri, '/logout') !== false) {
    $action = 'logout';
} else {
    if (isset($_GET['action'])) {
        $action = $_GET['action'];
    } elseif (isset($input['action'])) {
        $action = $input['action'];
    }
}

$authService = new AuthService($pdo);

try {
    switch ($action) {
        case 'register':
            if ($method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            $username = '';
            if (isset($input['username'])) {
                $username = trim($input['username']);
            }
            $password = '';
            if (isset($input['password'])) {
                $password = $input['password'];
            }
            
            $user = $authService->register($username, $password);
            setUserSession($user['id'], $user['username'], 'user');
            
            echo json_encode([
                'success' => true,
                'message' => 'Registration successful',
                'user' => $user
            ]);
            break;
            
        case 'login':
            if ($method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            $username = '';
            if (isset($input['username'])) {
                $username = trim($input['username']);
            }
            $password = '';
            if (isset($input['password'])) {
                $password = $input['password'];
            }
            
            $user = $authService->login($username, $password);
            setUserSession($user['id'], $user['username'], $user['role']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username']
                ]
            ]);
            break;
            
        case 'logout':
            if ($method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            destroyUserSession();
            
            echo json_encode([
                'success' => true,
                'message' => 'Logout successful'
            ]);
            break;
            
        case 'check':
            if (isLoggedIn()) {
                echo json_encode([
                    'success' => true,
                    'logged_in' => true,
                    'user' => [
                        'id' => getCurrentUserId(),
                        'username' => getCurrentUsername(),
                        'role' => getCurrentUserRole()
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'logged_in' => false
                ]);
            }
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
