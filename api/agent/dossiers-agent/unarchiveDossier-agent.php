<?php
session_start();
require_once '../../../config/headers.php';
require_once '../../../config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'agentC') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$dossierId = $data['id'] ?? null;

if (!$dossierId) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing dossier ID']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE dossiers SET archived = false WHERE id = ?");
    $stmt->execute([$dossierId]);

    echo json_encode(['message' => 'Dossier unarchived successfully']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error unarchiving dossier: ' . $e->getMessage()]);
}
