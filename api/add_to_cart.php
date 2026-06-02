<?php
session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$item_name = $data['name'];
$item_price = (float)$data['price'];
$action = $data['action'] ?? 'add';

if ($action === 'add') {
    if (isset($_SESSION['cart'][$item_name])) {
        $_SESSION['cart'][$item_name]['quantity']++;
    } else {
        $_SESSION['cart'][$item_name] = [
            'name' => $item_name,
            'price' => $item_price,
            'quantity' => 1
        ];
    }
} elseif ($action === 'update') {
    $new_qty = (int)$data['quantity'];
    if ($new_qty <= 0) {
        unset($_SESSION['cart'][$item_name]);
    } else {
        $_SESSION['cart'][$item_name]['quantity'] = $new_qty;
    }
} elseif ($action === 'remove') {
    unset($_SESSION['cart'][$item_name]);
}

// Calculate total
$total = 0;
$count = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
    $count += $item['quantity'];
}

echo json_encode([
    'success' => true,
    'cart' => $_SESSION['cart'],
    'total' => $total,
    'count' => $count
]);
?>