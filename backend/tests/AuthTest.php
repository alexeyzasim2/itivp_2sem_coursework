<?php

require_once __DIR__ . '/../repository/config/db.php';

class AuthTest {
    private $pdo;
    private $testUserIds = [];
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    private function cleanup() {
        foreach ($this->testUserIds as $userId) {
            $stmt = $this->pdo->prepare('DELETE FROM dreams WHERE user_id = ?');
            $stmt->execute([$userId]);
            
            $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = ?');
            $stmt->execute([$userId]);
        }
        $this->testUserIds = [];
    }
    
    public function testUserRegistration() {
        try {
            $username = 'test_register_' . time();
            $password = 'testpass123';
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $this->pdo->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
            $result = $stmt->execute([$username, $passwordHash]);
            
            assert($result === true, 'User registration should succeed');
            
            $userId = $this->pdo->lastInsertId();
            $this->testUserIds[] = $userId;
            
            assert($userId > 0, 'User ID should be greater than 0');
            
            $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            assert($user !== false, 'User should exist in database');
            assert($user['username'] === $username, 'Username should match');
            assert(password_verify($password, $user['password_hash']), 'Password should be hashed correctly');
            
            echo "User registration test: OK\n";
            
            return true;
        } catch (Exception $e) {
            echo "User registration test: FAILED - " . $e->getMessage() . "\n";
            return false;
        } finally {
            $this->cleanup();
        }
    }
    
    public function testDuplicateUsername() {
        try {
            $username = 'test_duplicate_' . time();
            $password = password_hash('testpass123', PASSWORD_DEFAULT);
            
            $stmt = $this->pdo->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
            $stmt->execute([$username, $password]);
            $this->testUserIds[] = $this->pdo->lastInsertId();
            
            $duplicateCreated = false;
            try {
                $stmt = $this->pdo->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
                $stmt->execute([$username, $password]);
                $duplicateCreated = true;
            } catch (PDOException $e) {
                $duplicateCreated = false;
            }
            
            assert($duplicateCreated === false, 'Duplicate username should not be allowed');
            
            echo "Duplicate username prevention test: OK\n";
            
            return true;
        } catch (Exception $e) {
            echo "Duplicate username prevention test: FAILED - " . $e->getMessage() . "\n";
            return false;
        } finally {
            $this->cleanup();
        }
    }
    
    public function testUserLogin() {
        try {
            $username = 'test_login_' . time();
            $password = 'testpass123';
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $this->pdo->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
            $stmt->execute([$username, $passwordHash]);
            $userId = $this->pdo->lastInsertId();
            $this->testUserIds[] = $userId;
            
            $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = ?');
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            assert($user !== false, 'User should be found by username');
            assert(password_verify($password, $user['password_hash']), 'Password should verify correctly');
            
            $wrongPasswordVerified = password_verify('wrongpassword', $user['password_hash']);
            assert($wrongPasswordVerified === false, 'Wrong password should not verify');
            
            echo "User login test: OK\n";
            
            return true;
        } catch (Exception $e) {
            echo "User login test: FAILED - " . $e->getMessage() . "\n";
            return false;
        } finally {
            $this->cleanup();
        }
    }
    
    public function runAll() {
        echo "\nRunning Authentication Tests\n\n";
        
        $results = [
            $this->testUserRegistration(),
            $this->testDuplicateUsername(),
            $this->testUserLogin()
        ];
        
        $passed = count(array_filter($results));
        $total = count($results);
        
        echo "\nAuth Tests: $passed/$total passed\n\n";
        
        return $passed === $total;
    }
}

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    try {
        $test = new AuthTest($pdo);
        $success = $test->runAll();
        exit($success ? 0 : 1);
    } catch (Exception $e) {
        echo "Fatal error: " . $e->getMessage() . "\n";
        exit(1);
    }
}

