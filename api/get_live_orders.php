<?php
include '../config/db.php';

// Pending ဖြစ်နေတဲ့ အော်ဒါတွေကို အသေးစိတ် item list နဲ့တကွ ဆွဲထုတ်မယ်
$res = $conn->query("SELECT o.id, o.table_no, o.created_at, 
                     GROUP_CONCAT(CONCAT(d.item_name, ' (x', d.qty, ')') SEPARATOR '<br>') as item_details
                     FROM orders o 
                     JOIN (
                        SELECT order_id, item_name, COUNT(*) as qty 
                        FROM order_details 
                        GROUP BY order_id, item_name
                     ) d ON o.id = d.order_id 
                     WHERE o.status = 'pending'
                     GROUP BY o.id
                     ORDER BY o.id DESC");

$orders = [];
while($row = $res->fetch_assoc()) {
    $row['time_formatted'] = date('h:i A', strtotime($row['created_at']));
    $orders[] = $row;
}

echo json_encode($orders);
?>