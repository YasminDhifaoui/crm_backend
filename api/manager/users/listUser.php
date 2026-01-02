<?php
session_start();
require_once '../../../config/headers.php';
require_once '../../../config/db.php';

if (!isset($_SESSION['user'])) {
    http_response_code(401); 
    echo json_encode(['error' => 'Not logged in']);
    exit;
}
if ($_SESSION['user']['role'] !== 'manager') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied: only manager can access']);
    exit;
}

try {
    $sql = 'SELECT * FROM users';
    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['message' => $users]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error fetching users: ' . $e->getMessage()]);
}