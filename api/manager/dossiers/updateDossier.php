<?php
session_start();
require_once '../../../config/headers.php';
require_once '../../../config/db.php';

// ✅ Check manager access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'manager') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied: only managers can update dossiers']);
    exit;
}

// ✅ Only allow POST or PUT
$method = $_SERVER['REQUEST_METHOD'];
if (!in_array($method, ['POST', 'PUT'])) {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$data = $_POST;

if (!isset($data['id']) || !is_numeric($data['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Données invalides : ID est requis']);
    exit;
}

$id = intval($data['id']);
$status = trim($data['status']);
$commentaire = isset($data['commentaire']) ? trim($data['commentaire']) : null;

// ✅ Handle file upload properly
$commentaire_file = null;
if (isset($_FILES['commentaire_file']) && $_FILES['commentaire_file']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../../../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $originalName = basename($_FILES['commentaire_file']['name']);
    $uniqueName = uniqid() . '_' . $originalName;
    $targetPath = $uploadDir . $uniqueName;

    if (move_uploaded_file($_FILES['commentaire_file']['tmp_name'], $targetPath)) {
        $commentaire_file = $uniqueName; // store only filename
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors du téléchargement du fichier.']);
        exit;
    }
}

// ✅ Only accept immatriculation if status is Livraison
$immatriculation = null;
if (strcasecmp($status, 'Livraison') === 0 && isset($data['immatriculation']) && is_string($data['immatriculation'])) {
    $immatriculation = trim($data['immatriculation']);
}

try {
    // ✅ Build SQL
    $sql = "UPDATE dossiers 
            SET status = :status,
                commentaire = :commentaire,
                updated_at = NOW()";

    if ($commentaire_file !== null) {
        $sql .= ", commentaire_file = :commentaire_file";
    }
    if ($immatriculation !== null) {
        $sql .= ", immatriculation = :immatriculation";
    }

    $sql .= " WHERE id = :id";

    $stmt = $pdo->prepare($sql);

    // ✅ Bind values
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':commentaire', $commentaire);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($commentaire_file !== null) {
        $stmt->bindParam(':commentaire_file', $commentaire_file);
    }

    if ($immatriculation !== null) {
        $stmt->bindParam(':immatriculation', $immatriculation);
    }

    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Dossier mis à jour avec succès']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de la mise à jour : ' . $e->getMessage()]);
}
