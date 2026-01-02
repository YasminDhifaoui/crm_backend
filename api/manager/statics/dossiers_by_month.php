<?php
session_start();
require_once '../../../config/db.php'; 
require_once '../../../config/headers.php'; 

try {
    $stmt = $pdo->prepare("
      SELECT 
        to_char(updated_at, 'Month') AS month_name,
        EXTRACT(MONTH FROM updated_at) AS month_number,
        status,
        COUNT(*) AS count
      FROM dossiers
      GROUP BY month_name, month_number, status
      ORDER BY month_number, status
    ");
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare response array with all months + all statuses to avoid missing months/statuses
    $allStatuses = ['Devis', 'Commande', 'Facturation', 'Livraison', 'Blockage'];
    $monthsOrdered = [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May',
        6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October',
        11 => 'November', 12 => 'December'
    ];

    // Initialize data with 0 counts
    $data = [];
    foreach ($monthsOrdered as $num => $name) {
        $data[$num] = ['month' => trim($name)];
        foreach ($allStatuses as $status) {
            $data[$num][$status] = 0;
        }
    }

    // Fill with actual counts
    foreach ($rows as $row) {
        $monthNum = (int)$row['month_number'];
        $status = $row['status'];
        $count = (int)$row['count'];

        if (isset($data[$monthNum]) && in_array($status, $allStatuses)) {
            $data[$monthNum][$status] = $count;
        }
    }

    // Re-index to remove month numbers as keys
    $data = array_values($data);

    echo json_encode($data);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
