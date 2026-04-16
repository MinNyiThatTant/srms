<?php include 'config/db.php'; ?>
<!DOCTYPE html>
<html lang="my">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Order System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #1a1a2e;
            color: white;
            font-family: sans-serif;
            padding-bottom: 100px;
        }

        .menu-card {
            background: #16213e;
            border: 1px solid #2e344e;
            border-radius: 15px;
        }

        .btn-add {
            background: #6366f1;
            color: white;
            border: none;
            transition: 0.3s;
        }

        .btn-add:hover {
            background: #4f46e5;
        }

        /* Floating Status Box */
        .status-tracker {
            position: fixed;
            top: 20px;
            right: 20px;
            width: 280px;
            background: white;
            color: #333;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: none;
            max-height: 300px;
            overflow-y: auto;
        }

        .status-item {
            border-bottom: 1px solid #eee;
            padding: 8px 0;
            font-size: 0.9rem;
        }

        .status-item:last-child {
            border: none;
        }

        .badge-pending {
            color: #f59e0b;
            font-weight: bold;
        }

        .badge-ready {
            color: #10b981;
            font-weight: bold;
        }

        .cart-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: #10b981;
            color: white;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div id="status-tracker" class="status-tracker">
        <h6 class="fw-bold border-bottom pb-2">My Orders Status</h6>
        <div id="status-list"></div>
    </div>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>🍽️ Table: <span id="table-id" class="text-info">--</span></h4>
        </div>

        <div class="row g-3">
            <?php
            $res = $conn->query("SELECT * FROM menu");
            while ($row = $res->fetch_assoc()): ?>
                <div class="col-6 col-md-4">
                    <div class="card menu-card h-100 p-2 text-center">
                        <div class="fw-bold"><?= $row['name'] ?></div>
                        <div class="text-info small mb-2"><?= number_format($row['price']) ?> MMK</div>
                        <button class="btn btn-sm btn-add" onclick="addToCart('<?= $row['name'] ?>', <?= $row['price'] ?>)">Add +</button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div class="cart-bar" onclick="placeOrder()">
        🛒 Confirm Order (<span id="cart-count">0</span>) - <span id="cart-total">0</span> MMK
    </div>

    <script>
        let cart = [];
        let myOrderIDs = [];
        const tableNo = new URLSearchParams(window.location.search).get('table') || "A1";
        document.getElementById('table-id').innerText = tableNo;

        function addToCart(name, price) {
            cart.push({
                name,
                price
            });
            document.getElementById('cart-count').innerText = cart.length;
            document.getElementById('cart-total').innerText = cart.reduce((s, i) => s + i.price, 0).toLocaleString();
        }

        async function placeOrder() {
            if (cart.length === 0) return alert("Menu အရင်ရွေးပါ");

            const res = await fetch('api/place_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    table: tableNo,
                    items: cart
                })
            });
            const result = await res.json();

            if (result.success) {
                alert("Order တင်ပြီးပါပြီ။ Chef ပြင်ပေးနေပါတယ်။");
                myOrderIDs.push(result.order_id);
                cart = [];
                document.getElementById('cart-count').innerText = "0";
                document.getElementById('cart-total').innerText = "0";

                document.getElementById('status-tracker').style.display = 'block';
                startLiveTracking();
            }
        }

        function startLiveTracking() {
            if (window.trackingInterval) clearInterval(window.trackingInterval);

            window.trackingInterval = setInterval(async () => {
                if (myOrderIDs.length === 0) {
                    document.getElementById('status-tracker').style.display = 'none';
                    clearInterval(window.trackingInterval);
                    return;
                }

                let html = "";
                for (let i = 0; i < myOrderIDs.length; i++) {
                    const id = myOrderIDs[i];
                    const res = await fetch(`api/check_status.php?id=${id}`);
                    const data = await res.json();

                    let isReady = data.status === 'ready';
                    let statusLabel = isReady ?
                        `<span class="badge bg-success shadow-sm">✅ Ready</span>` :
                        `<span class="badge bg-warning text-dark shadow-sm">⏳ Cooking...</span>`;

                    // Cancel ခလုတ် ထည့်သွင်းခြင်း (Pending ဖြစ်နေမှသာ ပြမယ်)
                    let cancelButton = !isReady ?
                        `<button class="btn btn-outline-danger btn-sm mt-2" style="font-size: 10px;" onclick="cancelOrder(${id})">❌ Cancel Order</button>` :
                        "";

                    // Item တွေကို list ပုံစံခွဲထုတ်မယ်
                    let itemsArray = data.item_details.split('|');
                    let itemsHtml = itemsArray.map(item => `
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-secondary">• ${item.split(' (')[0]}</span>
                    <span class="badge bg-light text-dark border">x ${item.split(' (')[1].replace(')', '')}</span>
                </div>
            `).join('');

                    // UI Card ပုံစံသစ်
                    html += `
                <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2" style="border-bottom: 1px dashed #ddd;">
                        <span class="fw-bold text-primary">Order #${id}</span>
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
                        }, 8000); // ၈ စက္ကန့်လောက် ပြပေးထားမယ်
                    }
                }
                document.getElementById('status-list').innerHTML = html;
            }, 3000);
        }


        async function cancelOrder(id) {
    if(confirm("ဒီအော်ဒါကို ဖျက်မှာ သေချာပါသလား?")) {
        const res = await fetch('api/cancel_order.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ id: id })
        });
        const result = await res.json();
        
        if(result.success) {
            alert("Order ကို Cancel လုပ်လိုက်ပါပြီ");
            // Local array ထဲကပါ ဖယ်ထုတ်မယ်
            myOrderIDs = myOrderIDs.filter(item => item !== id);
            // UI ကို ချက်ချင်း Update ဖြစ်သွားအောင် (သို့မဟုတ်) စာသားပြောင်းပေးမယ်
            document.getElementById(`order-card-${id}`).innerHTML = "<p class='text-danger p-2 small'>ဒီအော်ဒါကို Cancel လုပ်လိုက်ပါပြီ</p>";
            setTimeout(() => { startLiveTracking(); }, 2000); // ခဏနေရင် tracker ထဲက ဖျောက်မယ်
        } else {
            alert(result.message);
        }
    }
}
    </script>
</body>

</html>