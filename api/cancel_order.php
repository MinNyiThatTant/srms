<?php
include '../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

if($data && isset($data['id'])) {
    $id = (int)$data['id'];

    // delete status when pending
    $check = $conn->query("SELECT status FROM orders WHERE id = $id");
    $row = $check->fetch_assoc();

    if($row && $row['status'] == 'pending') {
        // delete from both tables
        $conn->query("DELETE FROM order_details WHERE order_id = $id");
        $conn->query("DELETE FROM orders WHERE id = $id");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Order အသင့်ပြင်ဆင်ပြီးဖြစ်၍ Cancel လုပ်၍မရတော့ကြောင်းတောင်းပန်အပ်ပါသည်။']);
    }
}
?>