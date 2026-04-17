<?php
include '../config/db.php';

$sql = "SELECT o.id, o.table_no, o.created_at, o.status,
        (SELECT GROUP_CONCAT(CONCAT(item_name, ' (x', qty, ')') SEPARATOR '<br>') 
         FROM (SELECT order_id, item_name, COUNT(*) as qty 
               FROM order_details 
               GROUP BY order_id, item_name) as d 
         WHERE d.order_id = o.id) as item_details
        FROM orders o 
        WHERE o.status = 'pending' 
        ORDER BY o.id DESC";

$res = $conn->query($sql);
$orders = [];

if($res) {
    while($row = $res->fetch_assoc()) {
        $row['time_formatted'] = date('h:i A', strtotime($row['created_at']));
        $row['item_details'] = $row['item_details'] ?: "No items";
        $orders[] = $row;
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($orders, JSON_UNESCAPED_UNICODE);
?>