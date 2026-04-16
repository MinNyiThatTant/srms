<?php
include '../config/db.php';
$id = $_GET['id'];

// Status နဲ့ မှာထားတဲ့ Item တွေကို အရေအတွက်အလိုက် Group ဖွဲ့ပြီး ဆွဲထုတ်မယ်
$res = $conn->query("SELECT o.status, 
                     GROUP_CONCAT(CONCAT(d.item_name, ' (', qty, ')') SEPARATOR '|') as item_details
                     FROM orders o 
                     JOIN (
                        SELECT order_id, item_name, COUNT(*) as qty 
                        FROM order_details 
                        GROUP BY order_id, item_name
                     ) d ON o.id = d.order_id 
                     WHERE o.id = $id 
                     GROUP BY o.id");

echo json_encode($res->fetch_assoc());
?>