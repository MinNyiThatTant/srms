<?php
include '../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

if($data && isset($data['id'])) {
    $id = (int)$data['id'];

    // Status က pending ဖြစ်နေမှသာ ဖျက်ခွင့်ပေးမယ်
    $check = $conn->query("SELECT status FROM orders WHERE id = $id");
    $row = $check->fetch_assoc();

    if($row && $row['status'] == 'pending') {
        // Table နှစ်ခုလုံးကနေ ဖျက်မယ်
        $conn->query("DELETE FROM order_details WHERE order_id = $id");
        $conn->query("DELETE FROM orders WHERE id = $id");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Admin မှ Ready လုပ်လိုက်ပြီဖြစ်၍ Cancel လုပ်၍မရတော့ပါ']);
    }
}
?>