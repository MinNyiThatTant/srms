<?php
include 'config/db.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Smart ကေတုအလင်္ကာ Restaurant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #0f172a;
            color: white;
            font-family: 'Segoe UI', sans-serif;
            padding-bottom: 100px;
        }

        .menu-card:hover {
            transform: translateY(-5px);
            border-color: #6366f1;
        }

        /* Quantity selector styles */
        .quantity-selector {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #2d3a4e;
            border-radius: 40px;
            padding: 5px;
            margin-top: 10px;
        }

        .qty-btn {
            background: #0f172a;
            border: none;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            font-weight: bold;
            font-size: 1.2rem;
            transition: 0.2s;
        }
        .qty-btn:hover {
            background: #6366f1;
        }
        .qty-display {
            font-size: 1.1rem;
            font-weight: bold;
            min-width: 30px;
            text-align: center;
        }

        /* Floating Status Toggle Button */
        .status-toggle-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #6366f1;
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            z-index: 1001;
            border: 2px solid white;
        }

        .status-tracker {
            position: fixed;
            top: 80px;
            right: 20px;
            width: 300px;
            background: #1e293b;
            border: 1px solid #6366f1;
            border-radius: 15px;
            padding: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: none;
        }

        @media (max-width: 576px) {
            .status-tracker {
                width: calc(100% - 40px);
                right: 20px;
                left: 20px;
            }
        }

        .menu-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-bottom: 1px solid #2e344e;
        }

        .menu-card {
            background: #1e293b;
            border: 1px solid #2e344e;
            border-radius: 20px;
            transition: 0.3s;
            overflow: hidden;
            height: 100%;
        }

        .item-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 5px;
            display: block;
        }

        .item-price {
            color: #94a3b8;
            font-size: 0.9rem;
        }

        .btn-add {
            background: #6366f1;
            color: white;
            border: none;
            transition: 0.3s;
            width: 100%;
            border-radius: 50px;
            font-weight: bold;
            padding: 10px;
            margin-top: 10px;
        }
        .btn-add:hover {
            background: #4f46e5;
            transform: scale(1.02);
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
            z-index: 999;
            border-radius: 20px 20px 0 0;
        }

        .category-scroll::-webkit-scrollbar {
            display: none;
        }

        .nav-pills .nav-link {
            color: #ccc;
            border-radius: 10px;
            margin: 0 2px;
        }
        .nav-pills .nav-link.active {
            background-color: #6366f1 !important;
            color: white;
        }
    </style>
</head>

<body>

    <div id="status-toggle" class="status-toggle-btn" onclick="toggleStatusBox()" style="display: none;">
        <span id="order-count-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px;">0</span>
        📋
    </div>

    <div id="status-tracker" class="status-tracker">
        <div class="d-flex justify-content-between align-items-center border-bottom border-secondary pb-2 mb-2">
            <h6 class="small fw-bold mb-0 text-info">📋 မှာယူထားသော အခြေအနေ</h6>
            <button class="btn-close btn-close-white" style="font-size: 10px;" onclick="toggleStatusBox()"></button>
        </div>
        <div id="status-list" style="max-height: 400px; overflow-y: auto;"></div>
    </div>

    <div class="container py-3">
        <div class="mb-3">
            <h5 class="fw-bold">Table: <span id="table-id" class="text-info">--</span></h5>
        </div>

        <ul class="nav nav-pills nav-justified mb-3 bg-dark p-1 shadow-sm" style="border-radius: 12px;" id="mainCatTabs">
        <?php
        $mcats = $conn->query("SELECT * FROM main_categories");
        $c = 0;
        while ($mc = $mcats->fetch_assoc()):
            $name = $mc['name'];
        ?>
            <li class="nav-item">
                <button class="nav-link <?= ($c == 0) ? 'active' : '' ?>"
                    onclick="filterMainCat('<?= $name ?>', this)">
                    <?= htmlspecialchars($mc['name']) ?>
                </button>
            </li>
        <?php $c++; endwhile; ?>
        </ul>

        <div class="row g-3" id="menu-container">
            <?php
            $res = $conn->query("SELECT * FROM menu");
            while ($row = $res->fetch_assoc()):
                $img = "uploads/" . ($row['image'] ?: 'default.jpg');
            ?>
                <div class="col-6 col-md-4 col-lg-3 mb-4 menu-item-card" data-main-cat="<?= $row['main_category'] ?>">
                    <div class="menu-card shadow-sm">
                        <img src="uploads/<?= $row['image'] ?>" class="menu-img" alt="<?= htmlspecialchars($row['name']) ?>">
                        <div class="p-3">
                            <strong class="item-name"><?= htmlspecialchars($row['name']) ?></strong>
                            <div class="item-price"><?= number_format($row['price']) ?> MMK</div>
                            
                            <!-- Quantity selector instead of simple Add button -->
                            <div class="quantity-selector" data-name="<?= htmlspecialchars($row['name']) ?>" data-price="<?= $row['price'] ?>">
                                <button class="qty-btn qty-minus">−</button>
                                <span class="qty-display">0</span>
                                <button class="qty-btn qty-plus">+</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div class="cart-bar shadow" onclick="placeOrder()">
        🛒 Confirm Order (<span id="cart-count">0</span> items) - <span id="cart-total">0</span> K
    </div>

    <script>
        // Cart object: key = item name, value = { price, quantity }
        let cartQuantities = {};
        let myOrderIDs = JSON.parse(localStorage.getItem('myOrders')) || [];
        const tableNo = new URLSearchParams(window.location.search).get('table') || "A1";
        document.getElementById('table-id').innerText = tableNo;

        // Update the bottom cart bar totals and item count
        function updateCartUI() {
            let totalItems = 0;
            let totalPrice = 0;
            for (let name in cartQuantities) {
                let qty = cartQuantities[name].quantity;
                let price = cartQuantities[name].price;
                totalItems += qty;
                totalPrice += qty * price;
            }
            document.getElementById('cart-count').innerText = totalItems;
            document.getElementById('cart-total').innerText = totalPrice.toLocaleString();
        }

        // Update the displayed quantity on the specific menu card
        function updateDisplayQuantity(itemName) {
            const selectorDiv = document.querySelector(`.quantity-selector[data-name="${itemName.replace(/"/g, '\\"')}"]`);
            if (selectorDiv) {
                const displaySpan = selectorDiv.querySelector('.qty-display');
                const currentQty = cartQuantities[itemName] ? cartQuantities[itemName].quantity : 0;
                displaySpan.innerText = currentQty;
            }
        }

        // Change quantity for a given item (delta = +1 or -1)
        function changeQuantity(itemName, price, delta) {
            if (!cartQuantities[itemName]) {
                cartQuantities[itemName] = { price: price, quantity: 0 };
            }
            let newQty = cartQuantities[itemName].quantity + delta;
            if (newQty < 0) newQty = 0;
            cartQuantities[itemName].quantity = newQty;
            // If quantity becomes 0, keep entry but we can optionally delete it (keeping is fine for simplicity)
            if (newQty === 0) {
                // optional: delete cartQuantities[itemName];
            }
            updateDisplayQuantity(itemName);
            updateCartUI();
        }

        // Event delegation for all quantity buttons (since menu items are generated by PHP)
        document.addEventListener('click', function(e) {
            const target = e.target;
            if (target.classList.contains('qty-plus') || target.classList.contains('qty-minus')) {
                const selectorDiv = target.closest('.quantity-selector');
                if (!selectorDiv) return;
                const itemName = selectorDiv.getAttribute('data-name');
                const price = parseFloat(selectorDiv.getAttribute('data-price'));
                const delta = target.classList.contains('qty-plus') ? 1 : -1;
                changeQuantity(itemName, price, delta);
                e.stopPropagation();
            }
        });

        // Place order: send all items with quantity > 0 to server
        async function placeOrder() {
            let itemsToSend = [];
            for (let name in cartQuantities) {
                let qty = cartQuantities[name].quantity;
                if (qty > 0) {
                    itemsToSend.push({
                        name: name,
                        price: cartQuantities[name].price,
                        quantity: qty
                    });
                }
            }
            if (itemsToSend.length === 0) {
                alert("ဟင်းပွဲအရင်ရွေးပါ (အနည်းဆုံး 1 ပွဲရွေးပါ)");
                return;
            }
            const res = await fetch('api/place_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    table: tableNo,
                    items: itemsToSend
                })
            });
            const result = await res.json();
            if (result.success) {
                myOrderIDs.push(result.order_id);
                localStorage.setItem('myOrders', JSON.stringify(myOrderIDs));
                alert("Order တင်ပြီးပါပြီ!");
                // Reset cart
                cartQuantities = {};
                // Reset all quantity displays to 0
                document.querySelectorAll('.quantity-selector .qty-display').forEach(span => span.innerText = '0');
                updateCartUI();
                location.reload(); // optional: reload to refresh status tracker
            } else {
                alert("Order မအောင်မြင်ပါ: " + (result.message || ""));
            }
        }

        function toggleStatusBox() {
            const box = document.getElementById('status-tracker');
            box.style.display = (box.style.display === 'none' || box.style.display === '') ? 'block' : 'none';
        }

        function startLiveTracking() {
            if (myOrderIDs.length === 0) {
                document.getElementById('status-toggle').style.display = 'none';
                return;
            }
            document.getElementById('status-toggle').style.display = 'flex';
            document.getElementById('order-count-badge').innerText = myOrderIDs.length;

            setInterval(async () => {
                let html = "";
                let activeOrders = 0;
                for (let id of myOrderIDs) {
                    try {
                        const res = await fetch(`api/check_status.php?id=${id}`);
                        const data = await res.json();
                        if (!data || data.status === 'not found') continue;
                        activeOrders++;
                        let isPending = (data.status.toLowerCase() === 'pending');
                        let isReady = (data.status.toLowerCase() === 'ready');
                        html += `
                        <div class="mb-3 p-2 border border-secondary rounded bg-dark shadow-sm">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <strong class="text-info" style="font-size: 12px;">Order #${id}</strong>
                                <span class="badge ${isPending ? 'bg-warning text-dark' : 'bg-success'}" style="font-size: 10px;">
                                    ${isPending ? '👨‍🍳 Cooking' : '✅ Ready'}
                                </span>
                            </div>
                            <div class="small text-light mb-2" style="font-size: 11px; opacity: 0.8;">
                                ${data.item_details ? data.item_details.replace(/\|/g, ', ') : ''}
                            </div>
                            ${isPending ? `
                                <button class="btn btn-danger btn-xs w-100 py-1" style="font-size: 10px;" onclick="cancelOrder(${id})">Cancel</button>
                            ` : ''}
                        </div>`;
                        if (isReady) {
                            setTimeout(() => {
                                myOrderIDs = myOrderIDs.filter(oid => oid != id);
                                localStorage.setItem('myOrders', JSON.stringify(myOrderIDs));
                            }, 60000);
                        }
                    } catch(e) {}
                }
                document.getElementById('status-list').innerHTML = html;
                document.getElementById('order-count-badge').innerText = activeOrders;
                if (activeOrders === 0) {
                    document.getElementById('status-toggle').style.display = 'none';
                    document.getElementById('status-tracker').style.display = 'none';
                }
            }, 5000);
        }

        async function cancelOrder(id) {
            if (!confirm('ဒီအော်ဒါကို ပယ်ဖျက်မှာ သေချာပါသလား?')) return;
            try {
                const res = await fetch('api/cancel_order.php', {
                    method: 'POST',
                    body: JSON.stringify({ id: id }),
                    headers: { 'Content-Type': 'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    alert('Order cancelled successfully');
                    location.reload();
                } else {
                    alert(data.message);
                }
            } catch(e) {
                console.error(e);
                alert('Error cancelling order');
            }
        }

        function filterMainCat(mainCat, element) {
            document.querySelectorAll('#mainCatTabs .nav-link').forEach(link => {
                link.classList.remove('active');
                link.style.background = 'transparent';
            });
            element.classList.add('active');
            element.style.background = '#6366f1';
            const cards = document.querySelectorAll('.menu-item-card');
            cards.forEach(card => {
                const cardCat = card.getAttribute('data-main-cat');
                card.style.display = (mainCat === 'all' || cardCat === mainCat) ? 'block' : 'none';
            });
        }

        window.onload = () => {
            const firstTab = document.querySelector('#mainCatTabs .nav-link.active');
            if (firstTab) firstTab.click();
            startLiveTracking();
        };
    </script>
</body>
</html>