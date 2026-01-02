<?php
session_start();
require_once '../../../config/headers.php';
require_once '../../../config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'agentC') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied: only agents can access']);
    exit;
}

try {
    $user_id = $_SESSION['user']['id']; // agent's ID

    $sql = "
        SELECT 
            d.*,
            CONCAT(u.nom, ' ', u.prenom) AS agent_full_name
        FROM dossiers d
        JOIN users u ON d.user_id = u.id
        WHERE d.archived = true AND d.user_id = :user_id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    $dossiers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['message' => $dossiers]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error fetching archived dossiers: ' . $e->getMessage()]);
}
