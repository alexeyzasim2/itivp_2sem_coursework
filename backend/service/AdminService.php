<?php

require_once __DIR__ . '/../repository/UserRepository.php';
require_once __DIR__ . '/../repository/DreamRepository.php';

class AdminService {
    private $userRepository;
    private $dreamRepository;
    
    public function __construct($pdo) {
        $this->userRepository = new UserRepository($pdo);
        $this->dreamRepository = new DreamRepository($pdo);
    }
    
    public function validateUserUpdate($data, $userId) {
        $errors = [];
        
        if (isset($data['username'])) {
            $username = trim($data['username']);
            if (empty($username)) {
                $errors[] = 'Username cannot be empty';
            } elseif ($this->userRepository->usernameExists($username, $userId)) {
                $errors[] = 'Username already exists';
            }
        }
        
        if (isset($data['role'])) {
            $role = trim($data['role']);
            if ($role !== 'user' && $role !== 'admin') {
                $errors[] = 'Invalid role';
            }
        }
        
        if (isset($data['password']) && !empty($data['password'])) {
            if (strlen($data['password']) < 6) {
                $errors[] = 'Password must be at least 6 characters';
            }
        }
        
        return $errors;
    }
    
    public function getAllUsers() {
        return $this->userRepository->findAll();
    }
    
    public function getUserById($userId) {
        $user = $this->userRepository->findById($userId);
        
        if (!$user) {
            throw new Exception('User not found');
        }
        
        return $user;
    }
    
    public function updateUser($userId, $data) {
        $errors = $this->validateUserUpdate($data, $userId);
        
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }
        
        $updateData = [];
        
        if (isset($data['username'])) {
            $updateData['username'] = trim($data['username']);
        }
        
        if (isset($data['role'])) {
            $updateData['role'] = trim($data['role']);
        }
        
        if (isset($data['password']) && !empty($data['password'])) {
            $updateData['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        if (empty($updateData)) {
            throw new Exception('No fields to update');
        }
        
        $this->userRepository->update($userId, $updateData);
        
        return $this->userRepository->findById($userId);
    }
    
    public function deleteUser($userId, $currentUserId) {
        if ($userId === $currentUserId) {
            throw new Exception('Cannot delete your own account');
        }
        
        $user = $this->userRepository->findById($userId);
        
        if (!$user) {
            throw new Exception('User not found');
        }
        
        return $this->userRepository->delete($userId);
    }
    
    public function getAllDreams() {
        return $this->dreamRepository->findAll();
    }
    
    public function deleteDream($dreamId) {
        $dream = $this->dreamRepository->findById($dreamId);
        
        if (!$dream) {
            throw new Exception('Dream not found');
        }
        
        return $this->dreamRepository->delete($dreamId);
    }
}

