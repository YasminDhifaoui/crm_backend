<?php
session_start();
include_once('../config/headers.php');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (isset($_SESSION['user'])) {
    header('Content-Type: application/json');
    echo json_encode(['user' => $_SESSION['user']]);
} else {
    http_response_code(401); // Unauthorized if no user session
    echo json_encode(['error' => 'No user session found']);
}