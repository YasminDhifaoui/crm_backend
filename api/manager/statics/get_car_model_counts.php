<?php
session_start();
require_once '../../../config/db.php'; 
require_once '../../../config/headers.php'; 

try {
    $stmt = $pdo->prepare("
        SELECT cm.category, COUNT(d.id) AS count
        FROM dossiers d
        JOIN car_models cm ON d.modeles = cm.model_name
        GROUP BY cm.category
        ORDER BY cm.category ASC
    ");
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$results) {
        echo json_encode([]);
        exit;
    }

    $categoryColors = [
        'Citroen' => '#0c4d24ff',
        'Peugeot' => '#eab308',
        'Opel' => '#dc2626',
        // add more categories if needed
    ];

    $data = array_map(function ($row) use ($categoryColors) {
        return [
            'name' => $row['category'],
            'progress' => (int)$row['count'],
            'fill' => $categoryColors[$row['category']] ?? '#6b7280', // default gray
        ];
    }, $results);

    echo json_encode($data);

} catch (PDOException $e) {
    // Return JSON error object with HTTP 500 status
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
