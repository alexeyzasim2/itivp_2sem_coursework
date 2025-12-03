<?php

require_once __DIR__ . '/../repository/UserRepository.php';

class AuthService {
    private $userRepository;
    
    public function __construct($pdo) {
        $this->userRepository = new UserRepository($pdo);
    }
    
    public function validateRegistration($username, $password) {
        $errors = [];
        
        if (empty($username)) {
            $errors[] = 'Username is required';
        } elseif (strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters';
        }
        
        if (empty($password)) {
            $errors[] = 'Password is required';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters';
        }
        
        if (!empty($username) && $this->userRepository->usernameExists($username)) {
            $errors[] = 'Username already exists';
        }
        
        return $errors;
    }
    
    public function register($username, $password) {
        $errors = $this->validateRegistration($username, $password);
        
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }
        
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $userId = $this->userRepository->create($username, $passwordHash);
        
        return [
            'id' => $userId,
            'username' => $username
        ];
    }
    
    public function validateLogin($username, $password) {
        $errors = [];
        
        if (empty($username)) {
            $errors[] = 'Username is required';
        }
        
        if (empty($password)) {
            $errors[] = 'Password is required';
        }
        
        return $errors;
    }
    
    public function login($username, $password) {
        $errors = $this->validateLogin($username, $password);
        
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }
        
        $user = $this->userRepository->findByUsername($username);
        
        if (!$user) {
            throw new Exception('Invalid username or password');
        }
        
        if (!password_verify($password, $user['password_hash'])) {
            throw new Exception('Invalid username or password');
        }
        
        return [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'] ?? 'user'
        ];
    }
}

