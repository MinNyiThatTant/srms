<?php
include '../config/db.php';
// search for the next ready order
$res = $conn->query("SELECT * FROM orders WHERE status = 'ready' ORDER BY id ASC LIMIT 1");
if($row = $res->fetch_assoc()) {
    echo json_encode($row);
    // mark as delivered
    $conn->query("UPDATE orders SET status = 'delivered' WHERE id = " . $row['id']);
} else {
    echo json_encode(['status' => 'none']);
}
?>