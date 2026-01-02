<?php
session_start();
require_once '../../../config/db.php'; 
require_once '../../../config/headers.php'; 

try {
    // Expect month in 'YYYY-MM' format (e.g., '2025-07')
    $month = $_GET['month'] ?? date('Y-m');

$sql = "
  SELECT 
    u.id AS user_id,
    CONCAT(u.prenom, ' ', u.nom) AS name,
    u.photopath AS imgPath,
    o.objective,
    o.realized AS realized
  FROM objectif o
  JOIN users u ON u.id = o.user_id
  WHERE TO_CHAR(o.month, 'YYYY-MM') = :month
  ORDER BY u.nom ASC
";
    $stmt = $pdo->prepare($sql);
$stmt->execute(['month' => $month]); 
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as &$row) {
    if (!empty($row['imgpath'])) {
        $row['imgPath'] = "/" . $row['imgpath'];
    } else {
        $row['imgPath'] = "/img/default-avatar.png";
    }unset($row['imgpath']);

}


    echo json_encode(['success' => true, 'data' => $results]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
