<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
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
$month = date('Y-m-01'); // First day of current month

try {
    // ✅ Count how many dossiers are "Facturation" for the current user this month
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS factured_count
        FROM dossiers
        WHERE status = 'Facturation'
          AND user_id = :user_id
          AND DATE_TRUNC('month', updated_at) = DATE_TRUNC('month', CURRENT_DATE)
    ");
    $stmt->execute([':user_id' => $userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $realizedCount = $row ? (int)$row['factured_count'] : 0;

    // ✅ Update objectif table
    $stmtUpdate = $pdo->prepare("
        UPDATE objectif
        SET realized = :realized, updated_at = NOW()
        WHERE user_id = :user_id AND month = :month
    ");
    $stmtUpdate->execute([
        ':realized' => $realizedCount,
        ':user_id' => $userId,
        ':month' => $month
    ]);

    // ✅ Update objectif_history table
    $stmtHistory = $pdo->prepare("
        UPDATE objectif_history
        SET realized = :realized, archived_at = NOW()
        WHERE user_id = :user_id AND month = :month
    ");
    $stmtHistory->execute([
        ':realized' => $realizedCount,
        ':user_id' => $userId,
        ':month' => $month
    ]);

    echo json_encode([
        'success' => true,
        'realized' => $realizedCount,
        'message' => 'Realized value updated successfully.'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to update realized',
        'details' => $e->getMessage()
    ]);
}
