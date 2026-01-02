<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../../../config/db.php';
require_once '../../../config/headers.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'agentC') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$userId = $_SESSION['user']['id'];
$month = date('Y-m-01');

try {
    $stmt = $pdo->prepare("SELECT objective, realized FROM objectif WHERE user_id = :user_id AND month = :month");
    $stmt->execute([':user_id' => $userId, ':month' => $month]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo json_encode([
            'success' => true,
            'objective' => (int)$row['objective'],
            'realized' => (int)$row['realized']
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'objective' => 0,
            'realized' => 0
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error', 'details' => $e->getMessage()]);
}
