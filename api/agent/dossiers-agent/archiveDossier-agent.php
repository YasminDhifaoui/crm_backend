<?php
session_start();
require_once '../../../config/headers.php';
require_once '../../../config/db.php';

// Only agentC role can archive
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'agentC') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

try {
    // Get dossier ID from POST data (or GET if you prefer)
    $input = json_decode(file_get_contents('php://input'), true);
    $dossierId = $input['id'] ?? null;

    if (!$dossierId) {
        http_response_code(400);
        echo json_encode(['error' => 'Dossier ID required']);
        exit;
    }

    // Update archived to true for this dossier AND make sure it's owned by this agent
    $sql = "UPDATE dossiers SET archived = true WHERE id = :id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id' => $dossierId,
        ':user_id' => $_SESSION['user']['id'],
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['message' => 'Dossier archived successfully']);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Dossier not found or not authorized']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
