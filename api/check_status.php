<?php
include '../config/db.php';

if(!isset($_GET['id'])) exit;
$id = (int)$_GET['id'];

// Status နဲ့ မှာထားတဲ့ Item တွေကို အရေအတွက်အလိုက် Group ဖွဲ့ပြီး ဆွဲထုတ်မယ်
$res = $conn->query("SELECT o.status, 
                     (SELECT GROUP_CONCAT(CONCAT(item_name, ' (x', qty, ')') SEPARATOR '|') 
                      FROM (SELECT order_id, item_name, COUNT(*) as qty 
                            FROM order_details 
                            GROUP BY order_id, item_name) as d 
                      WHERE d.order_id = o.id) as item_details
                     FROM orders o 
                     WHERE o.id = $id");

$data = $res->fetch_assoc();
header('Content-Type: application/json');
if($data) {
    echo json_encode($data);
} else {
    echo json_encode(['status' => 'not found']);
}
?>