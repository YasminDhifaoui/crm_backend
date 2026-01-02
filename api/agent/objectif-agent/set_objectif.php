<?php

session_start();
require_once '../../../config/headers.php';
require_once '../../../config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'agentC') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

$userId = $_SESSION['user']['id'];
$objective = isset($data['objective']) ? (int)$data['objective'] : 0;

// Validate
if ($objective <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Objective must be greater than zero']);
    exit;
}

//$month = date('Y-m-01'); // First day of current month
$month = isset($data['month']) ? date('Y-m-01', strtotime($data['month'])) : date('Y-m-01');


try {
$pdo->beginTransaction();
    // ✅ 1. Insert or update in `objectif` table
    $stmt = $pdo->prepare("
        INSERT INTO objectif (user_id, month, objective, updated_at)
        VALUES (:user_id, :month, :objective, NOW())
        ON CONFLICT (user_id, month)
        DO UPDATE SET objective = :objective, updated_at = NOW()
    ");
    $stmt->execute([
        ':user_id' => $userId,
        ':month' => $month,
        ':objective' => $objective
    ]);

    // ✅ 2. Insert into `objectif_history`
    $stmtHist = $pdo->prepare("
        INSERT INTO objectif_history (user_id, month, objective, realized, archived_at)
        VALUES (:user_id, :month, :objective, 0, NOW())
    ");
    $stmtHist->execute([
        ':user_id' => $userId,
        ':month' => $month,
        ':objective' => $objective
    ]);

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Objective saved']);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save objective', 'details' => $e->getMessage()]);
}
