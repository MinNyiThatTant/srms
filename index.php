<?php 
// ၁။ Database ချိတ်ဆက်မှုကို ထိပ်ဆုံးမှာ ထည့်ပါ
include 'config/db.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Order System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #0f172a; color: white; font-family: sans-serif; padding-bottom: 100px; }
        .menu-card { background: #16213e; border: 1px solid #2e344e; border-radius: 15px; transition: 0.3s; }
        .menu-card:hover { transform: translateY(-5px); border-color: #6366f1; }
        .btn-add { background: #6366f1; color: white; border: none; transition: 0.3s; width: 100%; border-radius: 8px; }
        .btn-add:hover { background: #4f46e5; }

        /* Floating Status Box */
        .status-tracker {
            position: fixed; top: 20px; right: 20px; width: 300px;
            background: white; color: #333; border-radius: 15px;
            padding: 15px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            z-index: 1000; display: none; max-height: 80vh; overflow-y: auto;
        }

        .cart-bar {
            position: fixed; bottom: 0; left: 0; width: 100%;
            background: #10b981; color: white; padding: 18px;
            text-align: center; cursor: pointer; font-weight: bold; font-size: 1.1rem;
            box-shadow: 0 -5px 15px rgba(0,0,0,0.2); z-index: 999;
        }
    </style>
</head>

<body>
    <div id="status-tracker" class="status-tracker shadow-lg">
        <h6 class="fw-bold border-bottom pb-2 mb-3">📋 My Orders Status</h6>
        <div id="status-list"></div>
    </div>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold">🍽️ Table: <span id="table-id" class="text-info">--</span></h4>
        </div>

        <div class="row g-3">
            <?php
            // ၂။ Connection ရှိမရှိ စစ်ပြီးမှ Query လုပ်မယ်
            if (isset($conn)) {
                $res = $conn->query("SELECT * FROM menu");
                while ($row = $res->fetch_assoc()): ?>
                    <div class="col-6 col-md-4">
                        <div class="card menu-card h-100 p-3 text-center border-0 shadow-sm">
                            <div class="fw-bold mb-1" style="font-size: 1.1rem;"><?= htmlspecialchars($row['name']) ?></div>
                            <div class="text-info small mb-3"><?= number_format($row['price']) ?> MMK</div>
                            <button class="btn btn-sm btn-add" onclick="addToCart('<?= addslashes($row['name']) ?>', <?= $row['price'] ?>)">Add to Cart +</button>
                        </div>
                    </div>
                <?php endwhile;
            } else {
                echo "<p class='text-danger'>Database connection error!</p>";
            } ?>
        </div>
    </div>

    <div class="cart-bar" onclick="placeOrder()">
        🛒 Confirm Order (<span id="cart-count">0</span>) - <span id="cart-total">0</span> MMK
    </div>

    <script>
        let cart = [];
        // ၃။ Refresh လုပ်ရင် မပျောက်အောင် localStorage ကနေ ID တွေ ပြန်ယူမယ်
        let myOrderIDs = JSON.parse(localStorage.getItem('my_orders')) || [];
        const tableNo = new URLSearchParams(window.location.search).get('table') || "A1";
        document.getElementById('table-id').innerText = tableNo;

        // Page Load ဖြစ်တာနဲ့ Tracking ရှိရင် ပြန်စမယ်
        window.onload = () => {
            if (myOrderIDs.length > 0) {
                document.getElementById('status-tracker').style.display = 'block';
                startLiveTracking();
            }
        };

        function addToCart(name, price) {
            cart.push({ name, price });
            updateCartUI();
        }

        function updateCartUI() {
            document.getElementById('cart-count').innerText = cart.length;
            document.getElementById('cart-total').innerText = cart.reduce((s, i) => s + i.price, 0).toLocaleString();
        }

        async function placeOrder() {
            if (cart.length === 0) return alert("Menu အရင်ရွေးပါ");

            const res = await fetch('api/place_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ table: tableNo, items: cart })
            });
            const result = await res.json();

            if (result.success) {
                alert("Order တင်ပြီးပါပြီ။");
                myOrderIDs.push(result.order_id);
                // ၄။ Storage မှာ သိမ်းမယ်
                localStorage.setItem('my_orders', JSON.stringify(myOrderIDs));
                
                cart = [];
                updateCartUI();
                document.getElementById('status-tracker').style.display = 'block';
                startLiveTracking();
            }
        }

        function startLiveTracking() {
            if (window.trackingInterval) clearInterval(window.trackingInterval);

            window.trackingInterval = setInterval(async () => {
                if (myOrderIDs.length === 0) {
                    document.getElementById('status-tracker').style.display = 'none';
                    localStorage.removeItem('my_orders');
                    clearInterval(window.trackingInterval);
                    return;
                }

                let html = "";
                for (let i = 0; i < myOrderIDs.length; i++) {
                    const id = myOrderIDs[i];
                    try {
                        const res = await fetch(`api/check_status.php?id=${id}`);
                        const data = await res.json();
                        if(!data) continue;

                        let isReady = data.status === 'ready';
                        let statusLabel = isReady ?
                            `<span class="badge bg-success shadow-sm">✅ Ready</span>` :
                            `<span class="badge bg-warning text-dark shadow-sm">⏳ Cooking...</span>`;

                        let cancelButton = !isReady ?
                            `<button class="btn btn-outline-danger btn-sm mt-2 w-100" style="font-size: 11px;" onclick="cancelOrder(${id})">❌ Cancel Order</button>` : "";

                        let itemsHtml = (data.item_details || "").split('|').map(item => {
                            let parts = item.split(' (');
                            return `<div class="d-flex justify-content-between align-items-center mb-1 small">
                                        <span class="text-secondary">• ${parts[0]}</span>
                                        <span class="badge bg-light text-dark border">x ${parts[1] ? parts[1].replace(')', '') : 1}</span>
                                    </div>`;
                        }).join('');

                        html += `
                        <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px; overflow: hidden;" id="card-${id}">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center py-2" style="border-bottom: 1px dashed #ddd;">
                                <span class="fw-bold text-primary small">Order #${id}</span>
                                ${statusLabel}
                            </div>
                            <div class="card-body py-2 px-3" style="background: #fdfdfd;">
                                ${itemsHtml}
                                ${cancelButton}
                            </div>
                        </div>`;

                        if (isReady) {
                            setTimeout(() => {
                                myOrderIDs = myOrderIDs.filter(item => item !== id);
                                localStorage.setItem('my_orders', JSON.stringify(myOrderIDs));
                            }, 15000); 
                        }
                    } catch (e) { console.error("Update error"); }
                }
                document.getElementById('status-list').innerHTML = html;
            }, 3000);
        }

        async function cancelOrder(id) {
            if (confirm("ဒီအော်ဒါကို ဖျက်မှာ သေချာပါသလား?")) {
                const res = await fetch('api/cancel_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                const result = await res.json();
                if (result.success) {
                    myOrderIDs = myOrderIDs.filter(item => item !== id);
                    localStorage.setItem('my_orders', JSON.stringify(myOrderIDs));
                    startLiveTracking();
                } else {
                    alert(result.message);
                }
            }
        }
    </script>
</body>
</html>