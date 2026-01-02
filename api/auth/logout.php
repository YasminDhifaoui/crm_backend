<?php
session_start();          // Start the session (important before destroying)
require_once '../../config/headers.php';

session_unset();          // Remove all session variables
session_destroy();        // Destroy the session
echo json_encode(["message" => "Logged out successfully"]);
?>
