<?php

function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        
        if (strpos($line, '=') === false) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (!array_key_exists($name, $_ENV)) {
            putenv("$name=$value");
            $_ENV[$name] = $value;
        }
    }
}


$envPaths = [
    __DIR__ . '/../../../.env',
    dirname(dirname(dirname(__DIR__))) . '/.env',
    $_SERVER['DOCUMENT_ROOT'] . '/.env',
    '/htdocs/.env'
];

foreach ($envPaths as $envPath) {
    if (file_exists($envPath)) {
        loadEnv($envPath);
        break;
    }
}

$host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'mysql';
$dbname = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'dreamjournal';
$username = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
$password = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: 'root';
$port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 10,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];
    $pdo = new PDO($dsn, $username, $password, $options);
    
    $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("SET CHARACTER SET utf8mb4");
    $pdo->exec("SET character_set_connection=utf8mb4");
    $pdo->exec("SET character_set_results=utf8mb4");
    $pdo->exec("SET character_set_client=utf8mb4");
} catch (PDOException $e) {
    http_response_code(500);
    $errorMessage = 'Database connection failed: ' . $e->getMessage() . 
                    ' (Host: ' . $host . ', DB: ' . $dbname . ', User: ' . $username . ')';
    echo json_encode(['error' => $errorMessage]);
    exit;
}

return $pdo;

