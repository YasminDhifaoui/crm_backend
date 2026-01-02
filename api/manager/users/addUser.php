<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../../config/headers.php';
require_once '../../../config/db.php';

// Check if manager is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'manager') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

// Input from multipart/form-data
$cin = $_POST['cin'] ?? '';
$nom = $_POST['nom'] ?? '';
$prenom = $_POST['prenom'] ?? '';
$telephone = $_POST['telephone'] ?? null;
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? '';

$allowedRoles = ['agentC', 'responsableV'];

// Validate
if (!$cin || !$nom || !$prenom || !$password || !in_array($role, $allowedRoles)) {
    http_response_code(400);
    echo json_encode(['error' => 'All fields are required']);
    exit;
}

// Check CIN uniqueness
$stmt = $pdo->prepare("SELECT id FROM users WHERE cin = :cin");
$stmt->execute(['cin' => $cin]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(['error' => 'CIN already exists']);
    exit;
}

// Handle image upload
$uploadDir = '../../../uploads/';
$photoPath = null;

if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $fileTmp = $_FILES['photo']['tmp_name'];
    $fileName = basename($_FILES['photo']['name']);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($fileExt, $allowedExt)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid image type']);
        exit;
    }

    $newFileName = uniqid() . '.' . $fileExt;
    $destination = $uploadDir . $newFileName;

    if (!move_uploaded_file($fileTmp, $destination)) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to upload image']);
        exit;
    }

    $photoPath = $newFileName;
}

// Hash password
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// Save user
try {
    $stmt = $pdo->prepare("INSERT INTO users (cin, nom, prenom, telephone, photopath, password, role)
        VALUES (:cin, :nom, :prenom, :telephone, :photopath, :password, :role)");

    $stmt->execute([
        'cin' => $cin,
        'nom' => $nom,
        'prenom' => $prenom,
        'telephone' => $telephone,
        'photopath' => $photoPath,
        'password' => $passwordHash,
        'role' => $role
    ]);

    http_response_code(201);
    echo json_encode(['message' => 'User added successfully']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
