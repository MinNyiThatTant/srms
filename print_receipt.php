<?php
include 'config/db.php';
$id = $_GET['id'];

// Order အချက်အလက်ယူမယ်
$order_res = $conn->query("SELECT * FROM orders WHERE id = $id");
$order = $order_res->fetch_assoc();

// Item အသေးစိတ်တွေကို ဈေးနှုန်းပါအောင် Join လုပ်ပြီးယူမယ်
$items_res = $conn->query("SELECT item_name, price, COUNT(*) as qty, (price * COUNT(*)) as subtotal 
                           FROM order_details 
                           WHERE order_id = $id 
                           GROUP BY item_name");
?>
<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <title>Receipt - #<?= $id ?></title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; width: 300px; margin: 0 auto; padding: 20px; color: #333; }
        .text-center { text-align: center; }
        .line { border-bottom: 1px dashed #000; margin: 10px 0; }
        table { width: 100%; font-size: 14px; }
        .total { font-weight: bold; font-size: 16px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">
    <div class="text-center">
        <h3 style="margin:0;">SMART ကေတုအလင်္ကာ RESTAURANT</h3>
        <p style="font-size: 12px;">ကေတုမတီမြို့သစ်။<br>Phone: 09-123456789</p>
    </div>

    <div class="line"></div>
    <p style="font-size: 13px;">
        Order ID: #<?= $id ?><br>
        Table: <?= $order['table_no'] ?><br>
        Date: <?= date('d-m-Y h:i A', strtotime($order['created_at'])) ?>
    </p>
    <div class="line"></div>

    <table>
        <thead>
            <tr>
                <th align="left">Item</th>
                <th align="center">Qty</th>
                <th align="right">Price</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $grand_total = 0;
            while($item = $items_res->fetch_assoc()): 
                $grand_total += $item['subtotal'];
            ?>
            <tr>
                <td><?= $item['item_name'] ?></td>
                <td align="center"><?= $item['qty'] ?></td>
                <td align="right"><?= number_format($item['subtotal']) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="line"></div>
    <table class="total">
        <tr>
            <td>Grand Total:</td>
            <td align="right"><?= number_format($grand_total) ?> MMK</td>
        </tr>
    </table>
    <div class="line"></div>

    <p class="text-center" style="font-size: 12px;">ကျေးဇူးတင်ပါသည်! နောက်လည်း ကြွခဲ့ပါ။</p>
    
    <div class="text-center no-print" style="margin-top: 20px;">
        <button onclick="window.close()" style="padding: 5px 15px; cursor:pointer;">Close</button>
    </div>
</body>
</html>