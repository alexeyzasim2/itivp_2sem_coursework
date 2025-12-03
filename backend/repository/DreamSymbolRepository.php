<?php

class DreamSymbolRepository {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function findAll() {
        $stmt = $this->pdo->query('SELECT word, variants, meaning FROM dream_symbols');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

