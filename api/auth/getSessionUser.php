<?php
session_start();

require_once '../../config/headers.php';
require_once '../../config/db.php';
if (!isset($_SESSION['user'])) {
    http_response_code(401); 
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

// If the user is logged in, return the user info
http_response_code(200);
echo json_encode(['user' => $_SESSION['user']]);
exit;
