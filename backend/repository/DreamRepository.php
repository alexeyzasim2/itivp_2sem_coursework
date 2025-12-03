<?php

class DreamRepository {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function findById($id, $userId = null) {
        if ($userId) {
            $stmt = $this->pdo->prepare('SELECT * FROM dreams WHERE id = ? AND user_id = ?');
            $stmt->execute([$id, $userId]);
        } else {
            $stmt = $this->pdo->prepare('SELECT * FROM dreams WHERE id = ?');
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }
    
    public function findByUserId($userId) {
        $stmt = $this->pdo->prepare('SELECT * FROM dreams WHERE user_id = ? ORDER BY dream_date DESC, created_at DESC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function findAll() {
        $stmt = $this->pdo->query('SELECT d.id, d.title, d.content, d.mood, d.dream_date, d.created_at, u.id as user_id, u.username FROM dreams d JOIN users u ON d.user_id = u.id ORDER BY d.created_at DESC');
        return $stmt->fetchAll();
    }
    
    public function create($userId, $title, $content, $mood, $dreamDate) {
        $stmt = $this->pdo->prepare('INSERT INTO dreams (user_id, title, content, mood, dream_date) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$userId, $title, $content, $mood, $dreamDate]);
        return $this->pdo->lastInsertId();
    }
    
    public function update($id, $userId, $data) {
        $updates = [];
        $params = [];
        
        if (isset($data['title'])) {
            $updates[] = 'title = ?';
            $params[] = $data['title'];
        }
        
        if (isset($data['content'])) {
            $updates[] = 'content = ?';
            $params[] = $data['content'];
        }
        
        if (isset($data['mood'])) {
            $updates[] = 'mood = ?';
            $params[] = $data['mood'];
        }
        
        if (isset($data['dream_date'])) {
            $updates[] = 'dream_date = ?';
            $params[] = $data['dream_date'];
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $params[] = $id;
        $params[] = $userId;
        
        $sql = 'UPDATE dreams SET ' . implode(', ', $updates) . ' WHERE id = ? AND user_id = ?';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount() > 0;
    }
    
    public function delete($id, $userId = null) {
        if ($userId) {
            $stmt = $this->pdo->prepare('DELETE FROM dreams WHERE id = ? AND user_id = ?');
            $stmt->execute([$id, $userId]);
        } else {
            $stmt = $this->pdo->prepare('DELETE FROM dreams WHERE id = ?');
            $stmt->execute([$id]);
        }
        return $stmt->rowCount() > 0;
    }
    
    public function getContentById($id, $userId) {
        $stmt = $this->pdo->prepare('SELECT content, title FROM dreams WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
        return $stmt->fetch();
    }
}

