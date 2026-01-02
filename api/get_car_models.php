<?php
session_start();
require_once '../config/headers.php';
require_once '../config/db.php';

try {

    $stmt = $pdo->query("SELECT * FROM car_models ORDER BY category, model_name");
    $carModels = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $carModels]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
