<?php
include '../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);
if($data) {
    $id = $data['id'];

    // အရေးကြီးချက်: Admin က ready လုပ်ပြီးသားဆိုရင် cancel လုပ်လို့မရအောင် စစ်ရမယ်
    $check = $conn->query("SELECT status FROM orders WHERE id = $id");
    $status = $check->fetch_assoc()['status'];

    if($status == 'pending') {
        // အော်ဒါအသေးစိတ်နဲ့ အော်ဒါအဓိက Table နှစ်ခုလုံးကနေ ဖျက်မယ်
        $conn->query("DELETE FROM order_details WHERE order_id = $id");
        $conn->query("DELETE FROM orders WHERE id = $id");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'ဟင်းပွဲပြင်ဆင်ပြီးသွားပြီဖြစ်၍ Cancel လုပ်၍မရတော့ပါ']);
    }
}
?>