<?php
session_start();
require_once '../../../config/db.php'; 
require_once '../../../config/headers.php'; 
 
try {
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM dossiers GROUP BY status");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $results
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

