<?php
session_start();
header('Content-Type: application/json');

unset($_SESSION['cart']);
echo json_encode(['success' => true]);
?>