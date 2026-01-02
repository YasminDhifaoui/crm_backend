<?php
session_start();
require_once '../../../config/headers.php';
require_once '../../../config/db.php';

// ✅ Check if manager is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'manager') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

// ✅ Parse JSON input
$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'] ?? null;
$nom = $data['nom'] ?? null;
$prenom = $data['prenom'] ?? null;
$telephone = $data['telephone'] ?? null;

// ✅ Basic validation
if (!$id || !$nom || !$prenom) {
    http_response_code(400);
    echo json_encode(['error' => 'ID, nom, and prenom are required']);
    exit;
}

// ✅ Prepare update
try {
    $stmt = $pdo->prepare("
        UPDATE users
        SET nom = :nom, prenom = :prenom, telephone = :telephone
        WHERE id = :id
    ");

    $stmt->execute([
        'id' => $id,
        'nom' => $nom,
        'prenom' => $prenom,
        'telephone' => $telephone,
    ]);

    http_response_code(200);
    echo json_encode(['message' => 'User updated successfully']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
