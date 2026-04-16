<?php
include '../config/db.php';
$data = json_decode(file_get_contents('php://input'), true);
if($data) {
    $table = $data['table'];
    $total = array_sum(array_column($data['items'], 'price'));
    $conn->query("INSERT INTO orders (table_no, total_price) VALUES ('$table', $total)");
    $oid = $conn->insert_id;
    foreach($data['items'] as $i) {
        $n = $i['name']; $p = $i['price'];
        $conn->query("INSERT INTO order_details (order_id, item_name, price) VALUES ($oid, '$n', $p)");
    }
    echo json_encode(['success' => true, 'order_id' => $oid]);
}
?>