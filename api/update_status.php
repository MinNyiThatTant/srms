<?php
include '../config/db.php';
$id = $_GET['id']; $status = $_GET['status'];
$conn->query("UPDATE orders SET status = '$status' WHERE id = $id");
echo json_encode(['success' => true]);
?>