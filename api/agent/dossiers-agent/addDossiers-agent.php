<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../../../config/headers.php';
require_once '../../../config/db.php';


if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'agentC') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied: only agentC can add dossiers']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

// ✅ Read and decode JSON data
$data = json_decode(file_get_contents("php://input"), true);

// ✅ Validate required fields
$requiredFields = ['nom_prenom_client', 'telephone', 'modeles', 'status'];
foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing required field: $field"]);
        exit;
    }
}

// If status is livraison, immatriculation is required
if ($data['status'] === 'livraison' && empty($data['immatriculation'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Immatriculation is required for livraison']);
    exit;
}

// Validate telephone: exactly 8 digits
if (!preg_match('/^\d{8}$/', $data['telephone'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Téléphone doit contenir exactement 8 chiffres']);
    exit;
}

// Validate immatriculation: max 18 alphanumeric chars or empty if status != livraison
if (strcasecmp($data['status'], 'Livraison') === 0) {
    if (!preg_match('/^[a-zA-Z0-9]{1,18}$/', $data['immatriculation'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Immatriculation doit être alphanumérique et jusqu\'à 18 caractères']);
        exit;
    }
} else {
    // Force empty immatriculation if status is not livraison
    $data['immatriculation'] = '';
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
        NOW(), 
        :user_id
    )
");

$stmt->execute([
    // No need to bind 'date_creation' anymore
    ':nom_prenom_client' => $data['nom_prenom_client'],
    ':telephone' => $data['telephone'],
    ':modeles' => $data['modeles'],
    ':commentaire' => $data['commentaire'],
    ':status' => $data['status'],
    ':immatriculation' => $data['immatriculation'],
    ':user_id' => $_SESSION['user']['id'],
]);


    echo json_encode(['message' => 'Dossier commercial ajouté avec succès']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de l’ajout du dossier: ' . $e->getMessage()]);
}
