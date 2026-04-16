<?php
include '../config/db.php';
// Ready ဖြစ်နေပြီး Robot မသွားရသေးတဲ့ အော်ဒါအဟောင်းဆုံးတစ်ခုကို ယူမယ်
$res = $conn->query("SELECT * FROM orders WHERE status = 'ready' ORDER BY id ASC LIMIT 1");
if($row = $res->fetch_assoc()) {
    echo json_encode($row);
    // Robot သိသွားပြီဖြစ်တဲ့အတွက် status ကို 'delivered' ပြောင်းလိုက်မယ် (ဒါမှ တစ်ခါပဲ သွားမှာ)
    $conn->query("UPDATE orders SET status = 'delivered' WHERE id = " . $row['id']);
} else {
    echo json_encode(['status' => 'none']);
}
?>