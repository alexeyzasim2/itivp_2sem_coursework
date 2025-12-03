<?php

class UserRepository {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function findByUsername($username) {
        $stmt = $this->pdo->prepare('SELECT id, username, password_hash, role FROM users WHERE username = ?');
        $stmt->execute([$username]);
        return $stmt->fetch();
    }
    
    public function findById($id) {
        $stmt = $this->pdo->prepare('SELECT id, username, role, created_at FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function findAll() {
        $stmt = $this->pdo->query('SELECT id, username, role, created_at FROM users ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }
    
    public function create($username, $passwordHash) {
        $stmt = $this->pdo->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
        $stmt->execute([$username, $passwordHash]);
        return $this->pdo->lastInsertId();
    }
    
    public function update($id, $data) {
        $updates = [];
        $params = [];
        
        if (isset($data['username'])) {
            $updates[] = 'username = ?';
            $params[] = $data['username'];
        }
        
        if (isset($data['role'])) {
            $updates[] = 'role = ?';
            $params[] = $data['role'];
        }
        
        if (isset($data['password_hash'])) {
            $updates[] = 'password_hash = ?';
            $params[] = $data['password_hash'];
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $params[] = $id;
        $sql = 'UPDATE users SET ' . implode(', ', $updates) . ' WHERE id = ?';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount() > 0;
    }
    
    public function delete($id) {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
    
    public function usernameExists($username, $excludeId = null) {
        if ($excludeId) {
            $stmt = $this->pdo->prepare('SELECT id FROM users WHERE username = ? AND id != ?');
            $stmt->execute([$username, $excludeId]);
        } else {
            $stmt = $this->pdo->prepare('SELECT id FROM users WHERE username = ?');
            $stmt->execute([$username]);
        }
        return $stmt->fetch() !== false;
    }
}

