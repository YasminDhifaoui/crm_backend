<?php
session_start();
require_once '../../../config/headers.php';
require_once '../../../config/db.php';

// Check if user is logged in and has the correct role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'agentC') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied: only agentC can access']);
    exit;
}

try {
    $params = [];
    $whereClauses = [];

    // Always filter by logged-in agent's user_id
    $whereClauses[] = "d.user_id = :user_id AND d.archived = false";
    $params[':user_id'] = $_SESSION['user']['id'];

    // Filter by status if provided and not 'Tous'
    if (isset($_GET['status']) && $_GET['status'] !== 'Tous') {
        $whereClauses[] = "d.status = :status";
        $params[':status'] = $_GET['status'];
    }

    // Build WHERE clause dynamically
    $whereClause = "";
    if (!empty($whereClauses)) {
        $whereClause = "WHERE " . implode(" AND ", $whereClauses);
    }

    $sql = "
        SELECT 
            d.*,
            CONCAT(u.nom, ' ', u.prenom) AS agent_full_name
        FROM dossiers d
        JOIN users u ON d.user_id = u.id
        $whereClause
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $dossiers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['message' => $dossiers]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error fetching dossiers: ' . $e->getMessage()]);
}
?>
