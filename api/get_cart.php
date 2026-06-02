<?php
session_start();
header('Content-Type: application/json');

$cart = $_SESSION['cart'] ?? [];
$total = 0;
$count = 0;

foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
    $count += $item['quantity'];
}

echo json_encode([
    'success' => true,
    'cart' => $cart,
    'total' => $total,
    'count' => $count
]);
?>