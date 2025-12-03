<?php

require_once __DIR__ . '/../repository/config/db.php';

class DreamTest {
    private $pdo;
    private $testUserId;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    private function setupTestUser() {
        $username = 'test_user_' . time();
        $password = password_hash('testpass123', PASSWORD_DEFAULT);
        
        $stmt = $this->pdo->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
        $stmt->execute([$username, $password]);
        
        $this->testUserId = $this->pdo->lastInsertId();
        echo "  Test user created (ID: {$this->testUserId})\n";
    }
    
    private function cleanupTestUser() {
        if ($this->testUserId) {
            $stmt = $this->pdo->prepare('DELETE FROM dreams WHERE user_id = ?');
            $stmt->execute([$this->testUserId]);
            
            $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = ?');
            $stmt->execute([$this->testUserId]);
            
            echo "  Test user cleaned up (ID: {$this->testUserId})\n";
        }
    }
    
    public function testCreateDream() {
        try {
            $this->setupTestUser();
            
            $title = 'Test Dream';
            $content = 'This is a test dream content';
            $mood = 'Счастливый';
            $dreamDate = date('Y-m-d');
            
            $stmt = $this->pdo->prepare(
                'INSERT INTO dreams (user_id, title, content, mood, dream_date) VALUES (?, ?, ?, ?, ?)'
            );
            $result = $stmt->execute([$this->testUserId, $title, $content, $mood, $dreamDate]);
            
            assert($result === true, 'Dream should be created successfully');
            
            $dreamId = $this->pdo->lastInsertId();
            assert($dreamId > 0, 'Dream ID should be greater than 0');
            
            $stmt = $this->pdo->prepare('SELECT * FROM dreams WHERE id = ?');
            $stmt->execute([$dreamId]);
            $dream = $stmt->fetch();
            
            assert($dream !== false, 'Created dream should be retrievable');
            assert($dream['title'] === $title, 'Dream title should match');
            assert($dream['content'] === $content, 'Dream content should match');
            assert($dream['mood'] === $mood, 'Dream mood should match');
            
            echo "Create dream test: OK\n";
            
            $this->cleanupTestUser();
            return true;
        } catch (Exception $e) {
            echo "Create dream test: FAILED - " . $e->getMessage() . "\n";
            $this->cleanupTestUser();
            return false;
        }
    }
    
    public function testReadDreams() {
        try {
            $this->setupTestUser();
            
            $dreams = [
                ['Test Dream 1', 'Content 1', 'Счастливый', date('Y-m-d')],
                ['Test Dream 2', 'Content 2', 'Спокойный', date('Y-m-d')],
                ['Test Dream 3', 'Content 3', 'Странный', date('Y-m-d')]
            ];
            
            foreach ($dreams as $dream) {
                $stmt = $this->pdo->prepare(
                    'INSERT INTO dreams (user_id, title, content, mood, dream_date) VALUES (?, ?, ?, ?, ?)'
                );
                $stmt->execute(array_merge([$this->testUserId], $dream));
            }
            
            $stmt = $this->pdo->prepare('SELECT * FROM dreams WHERE user_id = ?');
            $stmt->execute([$this->testUserId]);
            $results = $stmt->fetchAll();
            
            assert(count($results) === 3, 'Should retrieve 3 dreams');
            
            echo "Read dreams test: OK\n";
            
            $this->cleanupTestUser();
            return true;
        } catch (Exception $e) {
            echo "Read dreams test: FAILED - " . $e->getMessage() . "\n";
            $this->cleanupTestUser();
            return false;
        }
    }
    
    public function testUpdateDream() {
        try {
            $this->setupTestUser();
            
            $stmt = $this->pdo->prepare(
                'INSERT INTO dreams (user_id, title, content, mood, dream_date) VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([$this->testUserId, 'Original Title', 'Original Content', 'Счастливый', date('Y-m-d')]);
            $dreamId = $this->pdo->lastInsertId();
            
            $newTitle = 'Updated Title';
            $newContent = 'Updated Content';
            $newMood = 'Спокойный';
            
            $stmt = $this->pdo->prepare(
                'UPDATE dreams SET title = ?, content = ?, mood = ? WHERE id = ? AND user_id = ?'
            );
            $result = $stmt->execute([$newTitle, $newContent, $newMood, $dreamId, $this->testUserId]);
            
            assert($result === true, 'Dream should be updated successfully');
            assert($stmt->rowCount() === 1, 'One row should be affected');
            
            $stmt = $this->pdo->prepare('SELECT * FROM dreams WHERE id = ?');
            $stmt->execute([$dreamId]);
            $dream = $stmt->fetch();
            
            assert($dream['title'] === $newTitle, 'Dream title should be updated');
            assert($dream['content'] === $newContent, 'Dream content should be updated');
            assert($dream['mood'] === $newMood, 'Dream mood should be updated');
            
            echo "Update dream test: OK\n";
            
            $this->cleanupTestUser();
            return true;
        } catch (Exception $e) {
            echo "Update dream test: FAILED - " . $e->getMessage() . "\n";
            $this->cleanupTestUser();
            return false;
        }
    }
    
    public function testDeleteDream() {
        try {
            $this->setupTestUser();
            
            $stmt = $this->pdo->prepare(
                'INSERT INTO dreams (user_id, title, content, mood, dream_date) VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([$this->testUserId, 'Dream to Delete', 'Content', 'Счастливый', date('Y-m-d')]);
            $dreamId = $this->pdo->lastInsertId();
            
            $stmt = $this->pdo->prepare('DELETE FROM dreams WHERE id = ? AND user_id = ?');
            $result = $stmt->execute([$dreamId, $this->testUserId]);
            
            assert($result === true, 'Dream should be deleted successfully');
            assert($stmt->rowCount() === 1, 'One row should be affected');
            
            $stmt = $this->pdo->prepare('SELECT * FROM dreams WHERE id = ?');
            $stmt->execute([$dreamId]);
            $dream = $stmt->fetch();
            
            assert($dream === false, 'Dream should not exist after deletion');
            
            echo "Delete dream test: OK\n";
            
            $this->cleanupTestUser();
            return true;
        } catch (Exception $e) {
            echo "Delete dream test: FAILED - " . $e->getMessage() . "\n";
            $this->cleanupTestUser();
            return false;
        }
    }
    
    public function runAll() {
        echo "\nRunning Dream CRUD Tests\n\n";
        
        $results = [
            $this->testCreateDream(),
            $this->testReadDreams(),
            $this->testUpdateDream(),
            $this->testDeleteDream()
        ];
        
        $passed = count(array_filter($results));
        $total = count($results);
        
        echo "\nDream Tests: $passed/$total passed\n\n";
        
        return $passed === $total;
    }
}

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    try {
        $test = new DreamTest($pdo);
        $success = $test->runAll();
        exit($success ? 0 : 1);
    } catch (Exception $e) {
        echo "Fatal error: " . $e->getMessage() . "\n";
        exit(1);
    }
}

