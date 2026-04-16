<?php
session_start(); // Session ကို အရင်ဆုံး စတင်ခေါ်ယူရပါမယ်

// Session variable အားလုံးကို ဖျက်ပစ်မယ်
session_unset();

// Session တစ်ခုလုံးကို ဖျက်သိမ်းမယ်
session_destroy();

// အောင်မြင်စွာ ထွက်ပြီးရင် Admin page (Login page) ကို ပြန်ပို့မယ်
header("Location: admin.php");
exit();
?>