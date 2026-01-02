<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../../../config/headers.php';
require_once '../../../config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'manager') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied: only manager can add dossiers']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

// ✅ Get data from $_POST
$data = $_POST;

// ✅ Validate required fields
$requiredFields = ['nom_prenom_client', 'telephone', 'modeles', 'status'];
foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing required field: $field"]);
        exit;
    }
}

if ($data['status'] === 'livraison' && empty($data['immatriculation'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Immatriculation is required for livraison']);
    exit;
}

if (!preg_match('/^\d{8}$/', $data['telephone'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Téléphone doit contenir exactement 8 chiffres']);
    exit;
}

if (strcasecmp($data['status'], 'Livraison') === 0) {
    if (!preg_match('/^[a-zA-Z0-9]{1,18}$/', $data['immatriculation'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Immatriculation doit être alphanumérique et jusqu\'à 18 caractères']);
        exit;
    }
} else {
    $data['immatriculation'] = '';
}

// ✅ Handle file upload
$uploadDir = '../../../uploads/commentaire_files/';
$uploadedFilePath = null;

if (isset($_FILES['commentaire_file']) && $_FILES['commentaire_file']['error'] === UPLOAD_ERR_OK) {
    $originalName = basename($_FILES['commentaire_file']['name']);
    $uniqueName = uniqid() . '_' . $originalName;
    $destination = $uploadDir . $uniqueName;

    if (move_uploaded_file($_FILES['commentaire_file']['tmp_name'], $destination)) {
        $uploadedFilePath = '/commentaire_files/' . $uniqueName; // Relative path for DB
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors du téléchargement du fichier']);
        exit;
    }
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO dossiers (
            date_creation, 
            nom_prenom_client, 
            telephone, 
            modeles, 
            commentaire, 
            status, 
            immatriculation, 
            commentaire_file,
            updated_at,
            user_id
        ) VALUES (
            NOW(), 
            :nom_prenom_client, 
            :telephone, 
            :modeles, 
            :commentaire, 
            :status, 
            :immatriculation,
            :commentaire_file,
            NOW(), 
            :user_id
        )
    ");

    $stmt->execute([
        ':nom_prenom_client' => $data['nom_prenom_client'],
        ':telephone' => $data['telephone'],
        ':modeles' => $data['modeles'],
        ':commentaire' => $data['commentaire'] ?? '',
        ':status' => $data['status'],
        ':immatriculation' => $data['immatriculation'],
        ':commentaire_file' => $uploadedFilePath ?? null,
        ':user_id' => $_SESSION['user']['id'],
    ]);

    echo json_encode(['message' => 'Dossier commercial ajouté avec succès']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de l’ajout du dossier: ' . $e->getMessage()]);
}
