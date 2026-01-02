<?php
session_start();
require_once '../../../config/headers.php';
require_once '../../../config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'agentC') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$userId = $_SESSION['user']['id'];
$month = isset($_GET['month']) ? date('Y-m-01', strtotime($_GET['month'])) : date('Y-m-01');

try {
    $stmt = $pdo->prepare("SELECT objective FROM objectif WHERE user_id = :user_id AND month = :month");
    $stmt->execute([
        ':user_id' => $userId,
        ':month' => $month
    ]);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo json_encode(['objective' => $result['objective']]);
    } else {
        echo json_encode(['objective' => null]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch objective']);
}