<?php include 'config/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SRMS Admin - Kitchen & Menu</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; margin: 0; display: flex; }
        .sidebar { width: 250px; background: #1a1a2e; color: white; min-height: 100vh; padding: 20px; }
        .main-content { flex: 1; padding: 30px; }
        .card-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .order-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-top: 5px solid #6366f1; }
        .item-list { margin: 15px 0; padding: 0; list-style: none; }
        .item-list li { padding: 8px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; }
        .btn-ready { background: #10b981; color: white; border: none; padding: 10px; width: 100%; border-radius: 8px; cursor: pointer; font-weight: bold; }
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; background: #fbbf24; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>SRMS Admin</h2>
    <hr>
    <p>🟢 Kitchen Live</p>
    <p>🍔 Manage Menu</p>
    <p>📊 Reports</p>
</div>

<div class="main-content">
    <h1>👨‍🍳 Active Kitchen Orders</h1>
    <div class="card-grid" id="order-display">
        <?php
        // Pending ဖြစ်နေတဲ့ Order တွေကို အရင်ဆုံးပြမယ်
        $orders = $conn->query("SELECT * FROM orders WHERE status='pending' ORDER BY id DESC");
        while($o = $orders->fetch_assoc()):
            $oid = $o['id'];
        ?>
        <div class="order-card">
            <div style="display: flex; justify-content: space-between;">
                <h3>Table: <?= $o['table_no'] ?></h3>
                <span class="status-badge"><?= $o['status'] ?></span>
            </div>
            <ul class="item-list">
                <?php
                $details = $conn->query("SELECT * FROM order_details WHERE order_id = $oid");
                while($d = $details->fetch_assoc()):
                ?>
                <li>
                    <span><?= $d['item_name'] ?></span>
                    <b>x 1</b>
                </li>
                <?php endwhile; ?>
            </ul>
            <p>Total: <b><?= number_format($o['total_price']) ?> MMK</b></p>
            <button class="btn-ready" onclick="completeOrder(<?= $o['id'] ?>, <?= $o['table_no'] ?>)">
                Ready to Serve (Send Robot)
            </button>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<div class="main-content" style="margin-top: 30px; border-top: 2px solid #ddd; padding-top: 20px;">
    <h2>📋 Table & QR Manager</h2>
    <div class="card-grid">
        <?php 
        $tables = [1, 2, 3, 4, 5]; // စားပွဲအရေအတွက်
        $my_ip = "192.168.1.5";    // သင့် Laptop/Pi ရဲ့ IP ကို ဒီမှာ ပြင်ပါ
        foreach($tables as $t): 
            $qr_link = "http://$my_ip/srms/index.php?table=$t";
        ?>
        <div class="order-card" style="border-top: 5px solid #10b981;">
            <h3>Table - <?= $t ?></h3>
            <p style="font-size: 12px; color: #666; word-break: break-all;">
                Link: <?= $qr_link ?>
            </p>
            <a href="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= urlencode($qr_link) ?>" target="_blank">
                <button class="btn-ready" style="background: #3498db;">View QR Code</button>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    function completeOrder(orderId, tableNo) {
        if(confirm(`Order for Table ${tableNo} is ready?`)) {
            fetch(`api/update_status.php?id=${orderId}&status=ready`)
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert(`Robot is moving to Table ${tableNo}!`);
                    location.reload();
                }
            });
        }
    }

    // ၅ စက္ကန့်တစ်ခါ Order အသစ် ရှိမရှိ စစ်မယ်
    setInterval(() => {
        // ဒီနေရာမှာ AJAX နဲ့ Page မ Refresh ဘဲ စစ်လို့ရအောင် လုပ်လို့ရပါတယ်
        // အခုလောလောဆယ်တော့ ရှင်းအောင် အလိုအလျောက် reload လုပ်ခိုင်းထားမယ်
        // location.reload(); 
    }, 10000);
</script>
</body>
</html>