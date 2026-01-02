<?php

$host = getenv('PGHOST');
$port = getenv('PGPORT');
$db   = getenv('PGDATABASE');
$user = getenv('POSTGRES_USER');
$pass = getenv('PGPASSWORD');

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$db;";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "Connected to PostgreSQL successfully!";
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}
