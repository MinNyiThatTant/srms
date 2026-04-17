<?php
include '../config/db.php';
$data = json_decode(file_get_contents('php://input'), true);

if($data && !empty($data['items'])) {
    $table = $conn->real_escape_string($data['table']);
    $total = 0;
    foreach($data['items'] as $i) { $total += (int)$i['price']; }

    $conn->query("INSERT INTO orders (table_no, total_price, status) VALUES ('$table', $total, 'pending')");
    $oid = $conn->insert_id;

    foreach($data['items'] as $i) {
        $n = $conn->real_escape_string($i['name']);
        $p = (int)$i['price'];
        $conn->query("INSERT INTO order_details (order_id, item_name, price) VALUES ($oid, '$n', $p)");
    }
    echo json_encode(['success' => true, 'order_id' => $oid]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid Data']);
}
?>