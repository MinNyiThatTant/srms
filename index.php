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
        body {
            background: #0f172a;
            color: white;
            font-family: sans-serif;
            padding-bottom: 100px;
        }

        .menu-card {
            background: #16213e;
            border: 1px solid #2e344e;
            border-radius: 15px;
            transition: 0.3s;
        }

        .menu-card:hover {
            transform: translateY(-5px);
            border-color: #6366f1;
        }

        .btn-add {
            background: #6366f1;
            color: white;
            border: none;
            transition: 0.3s;
            width: 100%;
            border-radius: 8px;
        }

        .btn-add:hover {
            background: #4f46e5;
        }

        /* Floating Status Box */
        .status-tracker {
            position: fixed;
            top: 20px;
            right: 20px;
            width: 300px;
            background: white;
            color: #333;
            border-radius: 15px;
            padding: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: none;
            max-height: 80vh;
            overflow-y: auto;
        }

        .cart-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: #10b981;
            color: white;
            padding: 18px;
            text-align: center;
            cursor: pointer;
            font-weight: bold;
            font-size: 1.1rem;
            box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.2);
            z-index: 999;
        }

        /* Card ပုံစံကို ပိုလှအောင် ပြင်ခြင်း */
        .menu-card {
            background: #1e293b !important;
            /* နည်းနည်း ပိုလင်းတဲ့ နက်ပြာရောင် */
            border-radius: 20px !important;
        }

        .menu-card img {
            transition: transform 0.5s ease;
        }

        .menu-card:hover img {
            transform: scale(1.1);
            /* Hover လုပ်ရင် ပုံလေး နည်းနည်း ကြီးလာမယ် */
        }

        /* Button ကို ဝိုင်းဝိုင်းလေး လုပ်မယ် */
        .btn-add {
            border-radius: 50px !important;
            padding: 8px 15px;
            font-weight: bold;
            font-size: 12px;
        }
        /* အမျိုးအစား ခလုတ်တန်းအတွက် styling */
.category-scroll::-webkit-scrollbar {
    display: none; /* scrollbar ဖျောက်ထားမယ် */
}

.menu-card {
    transition: 0.3s;
}

/* ပုံကို card ထဲမှာ အပြည့်ပေါ်စေပြီး ဘေးတွေမပြတ်စေချင်ရင် object-fit: contain သုံးပါ */
/* ဒါပေမယ့် ပုံစံတူညီအောင် cover က ပိုလှပါတယ် */
.menu-card img {
    width: 100%;
    height: 100%;
    display: block;
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
            <h4 class="fw-bold text-white">🍽️ Table: <span id="table-id" class="text-info">--</span></h4>
        </div>

        <div class="category-scroll d-flex gap-2 overflow-auto pb-3 mb-4">
            <button class="btn btn-sm btn-outline-info active" onclick="filterMenu('all', event)">All</button>
            <button class="btn btn-sm btn-outline-info" onclick="filterMenu('cool drink', event)">Cool Drink</button>
            <button class="btn btn-sm btn-outline-info" onclick="filterMenu('hot drink', event)">Hot Drinks</button>
            <button class="btn btn-sm btn-outline-info" onclick="filterMenu('traditional food', event)">Traditional</button>
            <button class="btn btn-sm btn-outline-info" onclick="filterMenu('thai food', event)">Thai Food</button>
            <button class="btn btn-sm btn-outline-info" onclick="filterMenu('lunch', event)">Lunch</button>
            <button class="btn btn-sm btn-outline-info" onclick="filterMenu('dinner', event)">Dinner</button>
            <button class="btn btn-sm btn-outline-info" onclick="filterMenu('dessert', event)">Dessert</button>
        </div>

        <div class="row g-3">
    <?php 
    if (isset($conn)) {
        $res = $conn->query("SELECT * FROM menu");
        while ($row = $res->fetch_assoc()): 
            $img_path = "uploads/" . ($row['image'] ? $row['image'] : 'default.jpg');
            
            // ၁။ ဒီနေရာမှာ category ကို သန့်စင်လိုက်ပါတယ်
            $clean_category = trim(strtolower($row['category']));
    ?>
        <div class="col-6 col-md-4 menu-item" data-category="<?= htmlspecialchars($clean_category) ?>">
            <div class="card menu-card h-100 border-0 shadow-sm overflow-hidden">
                <div class="image-container" style="width: 100%; height: 150px; background: #242f44;">
                    <img src="<?= $img_path ?>" class="w-100 h-100" style="object-fit: cover;" alt="food">
                </div>

                <div class="p-3 text-center">
                    <div class="fw-bold mb-1 text-white" style="font-size: 0.95rem;">
                        <?= htmlspecialchars($row['name']) ?>
                    </div>
                    <div class="text-muted small mb-1" style="font-size: 10px;">(<?= $clean_category ?>)</div>
                    
                    <div class="text-info small mb-3 fw-bold">
                        <?= number_format($row['price']) ?> MMK
                    </div>
                    <button class="btn btn-sm btn-add" 
                            onclick="addToCart('<?= addslashes($row['name']) ?>', <?= $row['price'] ?>)">
                        Add to Cart +
                    </button>
                </div>
            </div>
        </div>
    <?php 
        endwhile; 
    } 
    ?>
</div>
    </div>

    <div class="cart-bar" onclick="placeOrder()">
        🛒 Confirm Order (<span id="cart-count">0</span>) - <span id="cart-total">0</span> MMK
    </div>

    <script>
    function filterMenu(category, event) {
    // Button Active ပြောင်းခြင်း
    const btns = document.querySelectorAll('.category-scroll .btn');
    btns.forEach(btn => btn.classList.remove('active'));
    event.currentTarget.classList.add('active');

    const items = document.querySelectorAll('.menu-item');
    
    // ရွေးချယ်လိုက်တဲ့ category ကို သန့်စင်လိုက်မယ်
    let filterCat = category.trim().toLowerCase();

    items.forEach(item => {
        // Item တစ်ခုချင်းစီရဲ့ category ကိုလည်း သန့်စင်ပြီးမှ တိုက်စစ်မယ်
        let itemCat = item.getAttribute('data-category').trim().toLowerCase();

        if (filterCat === 'all' || itemCat === filterCat) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

    let cart = [];
        // Refresh လုပ်ရင် data မပျောက်အောင် localStorage ကနေ အော်ဒါ ID တွေ ပြန်ယူမယ်
        let myOrderIDs = JSON.parse(localStorage.getItem('my_orders')) || [];
        
        // URL ကနေ Table No ကို ယူမယ် (မရှိရင် A1 လို့ သတ်မှတ်မယ်)
        const tableNo = new URLSearchParams(window.location.search).get('table') || "A1";
        document.getElementById('table-id').innerText = tableNo;

        // Page Load ဖြစ်တာနဲ့ Tracking ရှိရင် စစ်မယ်
        window.onload = () => {
            if (myOrderIDs.length > 0) {
                document.getElementById('status-tracker').style.display = 'block';
                startLiveTracking();
            }
        };

        // Cart ထဲကို ပစ္စည်းထည့်တဲ့ Function
        function addToCart(name, price) {
            cart.push({ name, price });
            updateCartUI();
        }

        // Cart Bar မှာ အရေအတွက်နဲ့ စုစုပေါင်းဈေးနှုန်းပြတဲ့ Function
        function updateCartUI() {
            document.getElementById('cart-count').innerText = cart.length;
            document.getElementById('cart-total').innerText = cart.reduce((s, i) => s + i.price, 0).toLocaleString();
        }

        // Order တင်တဲ့ Function
        async function placeOrder() {
            if (cart.length === 0) return alert("Menu အရင်ရွေးပါဦး Bro!");

            try {
                const res = await fetch('api/place_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ table: tableNo, items: cart })
                });
                const result = await res.json();

                if (result.success) {
                    alert("Order တင်ပြီးပါပြီ။ ခေတ္တစောင့်ဆိုင်းပေးပါ ခင်ဗျာ။");
                    myOrderIDs.push(result.order_id);
                    
                    // Browser storage မှာ သိမ်းထားမယ်
                    localStorage.setItem('my_orders', JSON.stringify(myOrderIDs));
                    
                    cart = []; // Cart ကို ပြန်ရှင်းမယ်
                    updateCartUI();
                    document.getElementById('status-tracker').style.display = 'block';
                    startLiveTracking(); // Tracking ပြန်စမယ်
                }
            } catch (error) {
                console.error("Order error:", error);
                alert("အော်ဒါတင်လို့ မရဖြစ်နေပါတယ်။ Network စစ်ပေးပါဦး။");
            }
        }

        // Order Status ကို Live လိုက်ကြည့်တဲ့ Function
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

                        // Item အသေးစိတ်တွေ ပြဖို့
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
                            <div class="card-body py-2 px-3" style="background: #fdfdfd; color: #333;">
                                ${itemsHtml}
                                ${cancelButton}
                            </div>
                        </div>`;

                        // Ready ဖြစ်ပြီး ၁၅ စက္ကန့်ကြာရင် List ထဲက ဖြုတ်မယ်
                        if (isReady) {
                            setTimeout(() => {
                                myOrderIDs = myOrderIDs.filter(item => item !== id);
                                localStorage.setItem('my_orders', JSON.stringify(myOrderIDs));
                            }, 15000); 
                        }
                    } catch (e) { console.error("Update error"); }
                }
                document.getElementById('status-list').innerHTML = html;
            }, 3000); // ၃ စက္ကန့်တစ်ခါ Update စစ်မယ်
        }

        // Order ပြန်ဖျက်တဲ့ Function
        async function cancelOrder(id) {
            if (confirm("ဒီအော်ဒါကို ဖျက်မှာ သေချာပါသလား?")) {
                try {
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
                } catch (error) {
                    alert("Cancel လုပ်လို့ မရပါ။");
                }
            }
        }
    </script>
</body>
</html>