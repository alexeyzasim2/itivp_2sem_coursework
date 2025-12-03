<?php

require_once __DIR__ . '/../repository/config/db.php';

class DatabaseTest {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function testConnection() {
        try {
            $this->pdo->query('SELECT 1');
            echo "Database connection test: OK\n";
            return true;
        } catch (PDOException $e) {
            echo "Database connection test: FAILED - " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    public function testUsersTableExists() {
        try {
            $stmt = $this->pdo->query("SHOW TABLES LIKE 'users'");
            $result = $stmt->fetch();
            
            assert($result !== false, 'Users table should exist');
            echo "Users table exists test: OK\n";
            return true;
        } catch (Exception $e) {
            echo "Users table exists test: FAILED - " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    public function testDreamsTableExists() {
        try {
            $stmt = $this->pdo->query("SHOW TABLES LIKE 'dreams'");
            $result = $stmt->fetch();
            
            assert($result !== false, 'Dreams table should exist');
            echo "Dreams table exists test: OK\n";
            return true;
        } catch (Exception $e) {
            echo "Dreams table exists test: FAILED - " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    public function testUsersTableStructure() {
        try {
            $stmt = $this->pdo->query("DESCRIBE users");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $requiredColumns = ['id', 'username', 'password_hash', 'created_at'];
            foreach ($requiredColumns as $column) {
                assert(in_array($column, $columns), "Users table should have $column column");
            }
            
            echo "Users table structure test: OK\n";
            return true;
        } catch (Exception $e) {
            echo "Users table structure test: FAILED - " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    public function testDreamsTableStructure() {
        try {
            $stmt = $this->pdo->query("DESCRIBE dreams");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $requiredColumns = ['id', 'user_id', 'title', 'content', 'mood', 'dream_date', 'created_at'];
            foreach ($requiredColumns as $column) {
                assert(in_array($column, $columns), "Dreams table should have $column column");
            }
            
            echo "Dreams table structure test: OK\n";
            return true;
        } catch (Exception $e) {
            echo "Dreams table structure test: FAILED - " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    public function runAll() {
        echo "\nRunning Database Tests\n\n";
        
        $results = [
            $this->testConnection(),
            $this->testUsersTableExists(),
            $this->testDreamsTableExists(),
            $this->testUsersTableStructure(),
            $this->testDreamsTableStructure()
        ];
        
        $passed = count(array_filter($results));
        $total = count($results);
        
        echo "\nDatabase Tests: $passed/$total passed\n\n";
        
        return $passed === $total;
    }
}

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    try {
        $test = new DatabaseTest($pdo);
        $success = $test->runAll();
        exit($success ? 0 : 1);
    } catch (Exception $e) {
        echo "Fatal error: " . $e->getMessage() . "\n";
        exit(1);
    }
}

