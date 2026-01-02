<?php
session_start();
require_once '../../../config/db.php'; 
require_once '../../../config/headers.php'; 

try {
    // Get month param or default to current month start
    $month = $_GET['month'] ?? date('Y-m-01');

    $sql = "
        SELECT
            SUM(objective) AS total_objective,
            SUM(realized) AS total_realized
        FROM objectif
        WHERE month = TO_DATE(:month, 'YYYY-MM-DD')
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['month' => $month]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Ensure default 0 values if no data
    $result = [
        'total_objective' => $result['total_objective'] ?? 0,
        'total_realized' => $result['total_realized'] ?? 0
    ];

    echo json_encode(['success' => true, 'data' => $result]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
