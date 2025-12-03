<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getCurrentUserId() {
    if (isset($_SESSION['user_id'])) {
        return $_SESSION['user_id'];
    }
    return null;
}

function getCurrentUsername() {
    if (isset($_SESSION['username'])) {
        return $_SESSION['username'];
    }
    return null;
}

function getCurrentUserRole() {
    if (isset($_SESSION['role'])) {
        return $_SESSION['role'];
    }
    return null;
}

function setUserSession($userId, $username, $role = 'user') {
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['role'] = $role;
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireAdmin() {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized. Please login first.']);
        exit;
    }
    if (!isAdmin()) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden. Admin access required.']);
        exit;
    }
}

function destroyUserSession() {
    session_unset();
    session_destroy();
}

function requireAuth() {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized. Please login first.']);
        exit;
    }
}

