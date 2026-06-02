<?php
include '../config/db.php';
header('Content-Type: application/json');

if(!isset($_GET['id'])) {
    echo json_encode(['status' => 'pending', 'item_details' => '']);
    exit;
}

$id = (int)$_GET['id'];

// First get the order status
$orderRes = $conn->query("SELECT status, table_no FROM orders WHERE id = $id");

if (!$orderRes || $orderRes->num_rows == 0) {
    // Order not found in database
    echo json_encode(['status' => 'pending', 'item_details' => '']);
    exit;
}

$order = $orderRes->fetch_assoc();
$status = strtolower(trim($order['status']));

// Get item details
$detailsRes = $conn->query("SELECT item_name, COUNT(*) as qty 
                            FROM order_details 
                            WHERE order_id = $id 
                            GROUP BY item_name");

$items = [];
if ($detailsRes && $detailsRes->num_rows > 0) {
    while ($row = $detailsRes->fetch_assoc()) {
        $items[] = $row['item_name'] . ' (x' . $row['qty'] . ')';
    }
}
$item_details = implode('|', $items);

// Return the actual status from database
echo json_encode([
    'status' => $status,
    'table_no' => $order['table_no'],
    'item_details' => $item_details ?: ''
]);
?>