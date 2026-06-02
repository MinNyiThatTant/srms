<?php
include 'config/db.php';
session_start();

$tableNo = isset($_SESSION['current_table']) ? $_SESSION['current_table'] : '';

if(empty($tableNo) && isset($_GET['table']) && !empty($_GET['table'])) {
    $tableNo = htmlspecialchars($_GET['table']);
    $_SESSION['current_table'] = $tableNo;
}

if(empty($tableNo)) {
    header("Location: index.php");
    exit;
}

// Get menu items with category names
$menuQuery = "SELECT m.*, mc.name as main_cat_name, sc.name as sub_cat_name 
              FROM menu m 
              LEFT JOIN main_categories mc ON m.main_category_id = mc.id 
              LEFT JOIN sub_categories sc ON m.sub_category_id = sc.id 
              ORDER BY m.id DESC";
$res = $conn->query($menuQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ကေတုအလင်္ကာ Smart Restaurant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #0f172a;
            color: white;
            font-family: 'Segoe UI', sans-serif;
            padding-bottom: 100px;
        }
        .menu-card:hover { transform: translateY(-5px); border-color: #6366f1; }
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
            cursor: pointer;
        }
        .qty-btn:hover { background: #6366f1; }
        .qty-display { font-size: 1.1rem; font-weight: bold; min-width: 30px; text-align: center; }
        
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
            z-index: 1001;
            border: 2px solid white;
        }
        .status-tracker {
            position: fixed;
            top: 80px;
            right: 20px;
            width: 350px;
            background: #1e293b;
            border: 1px solid #6366f1;
            border-radius: 15px;
            padding: 15px;
            z-index: 1000;
            display: none;
            max-height: 500px;
            overflow-y: auto;
        }
        @media (max-width: 576px) {
            .status-tracker { width: calc(100% - 40px); right: 20px; left: 20px; }
        }
        .menu-img { width: 100%; height: 180px; object-fit: cover; border-bottom: 1px solid #2e344e; }
        .menu-card { background: #1e293b; border: 1px solid #2e344e; border-radius: 20px; overflow: hidden; height: 100%; }
        .item-name { font-size: 1.1rem; font-weight: 600; color: #ffffff; display: block; }
        .item-price { color: #94a3b8; font-size: 0.9rem; }
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
        }
        .main-cat-tabs { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 15px; }
        .main-cat-btn { background: #1e293b; border: none; color: #ccc; padding: 8px 20px; border-radius: 30px; cursor: pointer; }
        .main-cat-btn.active { background: #6366f1; color: white; }
        .sub-cat-row { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #2d3a4e; }
        .sub-cat-btn { background: transparent; border: 1px solid #4f46e5; color: #a5b4fc; padding: 4px 14px; border-radius: 20px; cursor: pointer; }
        .sub-cat-btn.active { background: #4f46e5; color: white; }
        .filter-header { font-size: 0.7rem; color: #94a3b8; margin-right: 10px; display: inline-flex; align-items: center; }
        .order-status-card { background: #0f172a; border-radius: 10px; padding: 10px; margin-bottom: 10px; border-left: 3px solid #6366f1; }
        .order-status-card.pending { border-left-color: #f59e0b; }
        .order-status-card.ready { border-left-color: #10b981; }
        .btn-cancel { background: #dc3545; color: white; border: none; padding: 4px 12px; border-radius: 20px; font-size: 11px; cursor: pointer; }
        .position-absolute { position: absolute; }
        .top-0 { top: 0; }
        .start-100 { left: 100%; }
        .translate-middle { transform: translate(-50%, -50%); }
        .bg-danger { background: #dc3545; }
    </style>
</head>

<body>

    <!-- Status Toggle Button -->
    <div id="status-toggle" class="status-toggle-btn" style="display: none;">
        <span id="order-count-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px;">0</span>
        📋
    </div>

    <!-- Status Tracker Panel -->
    <div id="status-tracker" class="status-tracker">
        <div class="d-flex justify-content-between align-items-center border-bottom border-secondary pb-2 mb-2">
            <h6 class="small fw-bold mb-0 text-info">📋 မှာယူထားသော အခြေအနေ</h6>
            <button class="btn-close btn-close-white" style="font-size: 10px;" id="closeStatusBtn">×</button>
        </div>
        <div id="status-list" style="max-height: 400px; overflow-y: auto;">
            <div class="text-center text-muted small py-3">အော်ဒါများ စောင့်ဆိုင်းနေပါသည်...</div>
        </div>
    </div>

    <div class="container py-3">
        <div class="mb-3">
            <h5 class="fw-bold">စားပွဲအမှတ်: <span id="table-id" class="text-info">--</span></h5>
        </div>

        <!-- Main Category Filter -->
        <div class="main-cat-tabs" id="mainCatTabs">
            <button class="main-cat-btn active" data-main-cat="all">📋 အားလုံး</button>
            <?php
            $mainCats = $conn->query("SELECT * FROM main_categories ORDER BY name");
            while ($main = $mainCats->fetch_assoc()):
            ?>
                <button class="main-cat-btn" data-main-cat="<?= htmlspecialchars($main['name']) ?>">
                    📁 <?= htmlspecialchars($main['name']) ?>
                </button>
            <?php endwhile; ?>
        </div>

        <!-- Sub Category Filter Row -->
        <div id="subCatFilterRow" class="sub-cat-row">
            <span class="filter-header">🔍 အောက်ခံအမျိုးအစား:</span>
            <button class="sub-cat-btn active" data-sub-cat="all">အားလုံး</button>
        </div>

        <div class="row g-3" id="menu-container">
            <?php while ($row = $res->fetch_assoc()): ?>
                <div class="col-6 col-md-4 col-lg-3 mb-4 menu-item-card" 
                     data-main-cat="<?= htmlspecialchars($row['main_cat_name'] ?? '') ?>"
                     data-sub-cat="<?= htmlspecialchars($row['sub_cat_name'] ?? '') ?>">
                    <div class="menu-card shadow-sm">
                        <img src="uploads/<?= $row['image'] ?>" class="menu-img" alt="<?= htmlspecialchars($row['name']) ?>" onerror="this.src='uploads/default.jpg'">
                        <div class="p-3">
                            <strong class="item-name"><?= htmlspecialchars($row['name']) ?></strong>
                            <?php if(!empty($row['sub_cat_name'])): ?>
                                <div class="small text-muted"><?= htmlspecialchars($row['sub_cat_name']) ?></div>
                            <?php endif; ?>
                            <div class="item-price"><?= number_format($row['price']) ?> MMK</div>
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

    <div class="cart-bar shadow" id="placeOrderBtn">
        Order မှာယူမည် (<span id="cart-count">0</span> ပွဲ) - <span id="cart-total">0</span> ကျပ်
    </div>

 <script>
    // ==================== SUB CATEGORIES DATA ====================
    const subCategoriesData = <?php 
        $subData = [];
        $subQuery = $conn->query("SELECT sc.*, mc.name as main_cat_name 
                                  FROM sub_categories sc 
                                  LEFT JOIN main_categories mc ON sc.main_category_id = mc.id 
                                  ORDER BY mc.name, sc.name");
        while($sub = $subQuery->fetch_assoc()) {
            $mainCatName = $sub['main_cat_name'];
            if(!isset($subData[$mainCatName])) {
                $subData[$mainCatName] = [];
            }
            $subData[$mainCatName][] = ['id' => $sub['id'], 'name' => $sub['name']];
        }
        echo json_encode($subData);
    ?>;
    
    // ==================== GLOBAL VARIABLES ====================
    let cartQuantities = {};
    let myOrders = [];          // array of order IDs for current table only
    let currentMainCat = 'all';
    let currentSubCat = 'all';
    let statusInterval = null;
    
    // Get current table number from URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentTableNo = urlParams.get('table') || localStorage.getItem('current_table') || "1";
    localStorage.setItem('current_table', currentTableNo);
    document.getElementById('table-id').innerText = currentTableNo;
    
    // ==================== STORAGE (Table-specific) ====================
    function getStorageKey() {
        return `myOrders_table_${currentTableNo}`;
    }
    
    function loadOrdersFromStorage() {
        try {
            const key = getStorageKey();
            const stored = localStorage.getItem(key);
            if (stored && stored !== 'undefined' && stored !== 'null' && stored !== '') {
                const parsed = JSON.parse(stored);
                if (Array.isArray(parsed)) {
                    // Filter valid IDs
                    myOrders = parsed.filter(id => id > 0);
                } else {
                    myOrders = [];
                }
            } else {
                myOrders = [];
            }
        } catch(e) {
            myOrders = [];
        }
        updateStatusButton();
        if (myOrders.length > 0) {
            setTimeout(() => updateStatusDisplay(), 100);
        }
    }
    
    function saveOrdersToStorage() {
        const key = getStorageKey();
        localStorage.setItem(key, JSON.stringify(myOrders));
        updateStatusButton();
    }
    
    function updateStatusButton() {
        const toggleBtn = document.getElementById('status-toggle');
        const badge = document.getElementById('order-count-badge');
        if (!toggleBtn) return;
        
        if (myOrders.length > 0) {
            toggleBtn.style.display = 'flex';
            if (badge) badge.innerText = myOrders.length;
        } else {
            toggleBtn.style.display = 'none';
            const box = document.getElementById('status-tracker');
            if (box) box.style.display = 'none';
        }
    }
    
    // ==================== CART FUNCTIONS ====================
    async function loadCartFromServer() {
        try {
            const res = await fetch('api/get_cart.php');
            const data = await res.json();
            if (data.success && data.cart) {
                cartQuantities = {};
                for (let name in data.cart) {
                    cartQuantities[name] = {
                        price: data.cart[name].price,
                        quantity: data.cart[name].quantity
                    };
                }
                for (let name in cartQuantities) {
                    updateDisplayQuantity(name);
                }
                updateCartUI();
            }
        } catch(e) {}
    }

    function updateCartUI() {
        let totalItems = 0, totalPrice = 0;
        for (let name in cartQuantities) {
            totalItems += cartQuantities[name].quantity;
            totalPrice += cartQuantities[name].quantity * cartQuantities[name].price;
        }
        const countEl = document.getElementById('cart-count');
        const totalEl = document.getElementById('cart-total');
        if (countEl) countEl.innerText = totalItems;
        if (totalEl) totalEl.innerText = totalPrice.toLocaleString();
    }

    function updateDisplayQuantity(itemName) {
        const selectorDiv = document.querySelector(`.quantity-selector[data-name="${itemName.replace(/"/g, '\\"')}"]`);
        if (selectorDiv) {
            const displaySpan = selectorDiv.querySelector('.qty-display');
            const currentQty = cartQuantities[itemName] ? cartQuantities[itemName].quantity : 0;
            if (displaySpan) displaySpan.innerText = currentQty;
        }
    }

    async function syncCartToServer(itemName, price, action, newQuantity) {
        try {
            let payload = { name: itemName, price: price, action: action };
            if (newQuantity !== undefined) payload.quantity = newQuantity;
            const res = await fetch('api/add_to_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                cartQuantities = {};
                for (let name in data.cart) {
                    cartQuantities[name] = { price: data.cart[name].price, quantity: data.cart[name].quantity };
                }
                updateCartUI();
                return true;
            }
            return false;
        } catch(e) { return false; }
    }

    async function changeQuantity(itemName, price, delta) {
        if (!cartQuantities[itemName]) cartQuantities[itemName] = { price: price, quantity: 0 };
        let newQty = cartQuantities[itemName].quantity + delta;
        if (newQty < 0) newQty = 0;
        cartQuantities[itemName].quantity = newQty;
        updateDisplayQuantity(itemName);
        updateCartUI();
        if (newQty === 0) {
            await syncCartToServer(itemName, price, 'remove');
        } else {
            await syncCartToServer(itemName, price, 'update', newQty);
        }
    }

    // ==================== FILTER FUNCTIONS ====================
    function updateSubCategoryFilterUI() {
        const subFilterRow = document.getElementById('subCatFilterRow');
        if (!subFilterRow) return;
        
        let html = '<span class="filter-header">🔍 အောက်ခံအမျိုးအစား:</span>';
        html += '<button class="sub-cat-btn active" data-sub-cat="all">အားလုံး</button>';
        
        if (currentMainCat !== 'all' && subCategoriesData[currentMainCat]) {
            subCategoriesData[currentMainCat].forEach(sub => {
                html += `<button class="sub-cat-btn" data-sub-cat="${escapeHtml(sub.name)}">📌 ${escapeHtml(sub.name)}</button>`;
            });
        } else if (currentMainCat === 'all') {
            const allSubs = {};
            for (let cat in subCategoriesData) {
                subCategoriesData[cat].forEach(sub => {
                    if (!allSubs[sub.name]) {
                        allSubs[sub.name] = true;
                        html += `<button class="sub-cat-btn" data-sub-cat="${escapeHtml(sub.name)}">📌 ${escapeHtml(sub.name)}</button>`;
                    }
                });
            }
        }
        
        subFilterRow.innerHTML = html;
        document.querySelectorAll('.sub-cat-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const subCat = this.getAttribute('data-sub-cat');
                document.querySelectorAll('.sub-cat-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentSubCat = subCat;
                applyFilters();
            });
        });
    }
    
    function applyFilters() {
        const cards = document.querySelectorAll('.menu-item-card');
        cards.forEach(card => {
            const cardMainCat = card.getAttribute('data-main-cat');
            const cardSubCat = card.getAttribute('data-sub-cat');
            const showByMain = (currentMainCat === 'all' || cardMainCat === currentMainCat);
            const showBySub = (currentSubCat === 'all' || cardSubCat === currentSubCat);
            card.style.display = (showByMain && showBySub) ? 'block' : 'none';
        });
    }
    
    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, m => m === '&' ? '&amp;' : m === '<' ? '&lt;' : '&gt;');
    }

    // ==================== EVENT LISTENERS ====================
    function setupEventListeners() {
        document.querySelectorAll('.main-cat-btn').forEach(btn => {
            btn.removeEventListener('click', mainCatClickHandler);
            btn.addEventListener('click', mainCatClickHandler);
        });
        document.querySelectorAll('.qty-plus, .qty-minus').forEach(btn => {
            btn.removeEventListener('click', quantityClickHandler);
            btn.addEventListener('click', quantityClickHandler);
        });
        const placeOrderBtn = document.getElementById('placeOrderBtn');
        if (placeOrderBtn) {
            placeOrderBtn.removeEventListener('click', placeOrder);
            placeOrderBtn.addEventListener('click', placeOrder);
        }
        const statusToggle = document.getElementById('status-toggle');
        if (statusToggle) {
            statusToggle.removeEventListener('click', toggleStatusBox);
            statusToggle.addEventListener('click', toggleStatusBox);
        }
        const closeBtn = document.getElementById('closeStatusBtn');
        if (closeBtn) {
            closeBtn.removeEventListener('click', closeStatusBox);
            closeBtn.addEventListener('click', closeStatusBox);
        }
    }
    
    function mainCatClickHandler(e) {
        const btn = e.currentTarget;
        const mainCat = btn.getAttribute('data-main-cat');
        document.querySelectorAll('.main-cat-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentMainCat = mainCat;
        currentSubCat = 'all';
        updateSubCategoryFilterUI();
        applyFilters();
    }
    
    function quantityClickHandler(e) {
        e.stopPropagation();
        const btn = e.currentTarget;
        const selectorDiv = btn.closest('.quantity-selector');
        if (!selectorDiv) return;
        const itemName = selectorDiv.getAttribute('data-name');
        const price = parseFloat(selectorDiv.getAttribute('data-price'));
        const delta = btn.classList.contains('qty-plus') ? 1 : -1;
        changeQuantity(itemName, price, delta);
    }
    
    function closeStatusBox() {
        const box = document.getElementById('status-tracker');
        if (box) box.style.display = 'none';
    }
    
    function toggleStatusBox() {
        const box = document.getElementById('status-tracker');
        if (!box) return;
        if (box.style.display === 'none' || box.style.display === '') {
            box.style.display = 'block';
            updateStatusDisplay();
        } else {
            box.style.display = 'none';
        }
    }

    // ==================== ORDER ====================
    async function placeOrder() {
        const itemsToSend = [];
        for (let name in cartQuantities) {
            const qty = cartQuantities[name].quantity;
            if (qty > 0) {
                itemsToSend.push({
                    name: name,
                    price: cartQuantities[name].price,
                    quantity: qty
                });
            }
        }
        if (itemsToSend.length === 0) {
            alert("ဟင်းပွဲအရင်ရွေးပါ");
            return;
        }
        try {
            const res = await fetch('api/place_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ table: currentTableNo, items: itemsToSend })
            });
            const result = await res.json();
            if (result.success && result.order_id) {
                myOrders.push(result.order_id);
                saveOrdersToStorage();
                alert("Order တင်ပြီးပါပြီ! Order ID: " + result.order_id);
                await fetch('api/clear_cart.php', { method: 'POST' });
                cartQuantities = {};
                document.querySelectorAll('.quantity-selector .qty-display').forEach(span => span.innerText = '0');
                updateCartUI();
                updateStatusDisplay();
            } else {
                alert("Order မအောင်မြင်ပါ");
            }
        } catch(e) {
            alert("Order မအောင်မြင်ပါ");
        }
    }

    // ==================== STATUS TRACKING (Fixed) ====================
    // Replace the entire updateStatusDisplay function in menu.php with this:

async function updateStatusDisplay() {
    updateStatusButton();
    
    const statusListEl = document.getElementById('status-list');
    if (!statusListEl) return;
    
    if (myOrders.length === 0) {
        statusListEl.innerHTML = '<div class="text-center text-muted small py-3">မှာယူထားသောအော်ဒါမရှိပါ</div>';
        return;
    }
    
    const fetchPromises = myOrders.map(async (id) => {
        try {
            const res = await fetch(`api/check_status.php?id=${id}`);
            const data = await res.json();
            return { id, data, error: false };
        } catch(e) {
            return { id, data: { status: 'pending', item_details: '' }, error: true };
        }
    });
    
    const results = await Promise.all(fetchPromises);
    let html = "";
    
    for (const result of results) {
        const id = result.id;
        const statusData = result.data;
        const orderStatus = (statusData.status || 'pending').toLowerCase();
        const isReady = (orderStatus === 'ready');
        const isPending = (orderStatus === 'pending');
        
        let itemDetails = '';
        if (statusData.item_details) {
            const items = statusData.item_details.split('|');
            itemDetails = items.map(item => `<div class="small text-muted">• ${item}</div>`).join('');
        } else {
            itemDetails = '<div class="small text-muted">ပစ္စည်းများ စောင့်ဆိုင်းနေပါသည်...</div>';
        }
        
        let statusText = '';
        let statusClass = '';
        let showCancel = false;
        
        if (isReady) {
            statusText = '✅ အသင့်ဖြစ်ပါပြီ';
            statusClass = 'ready';
            showCancel = false;
        } else {
            statusText = '👨‍🍳 ချက်ပြုတ်နေပါသည်';
            statusClass = 'pending';
            showCancel = true;   // For pending or unknown status, show cancel button
        }
        
        html += `
            <div class="order-status-card ${statusClass}">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <strong class="text-info" style="font-size: 13px;">📌 Order #${id}</strong>
                    <span class="badge ${isReady ? 'bg-success' : 'bg-warning text-dark'}">${statusText}</span>
                </div>
                <div class="mb-2">${itemDetails}</div>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">စားပွဲအမှတ်: ${currentTableNo}</small>
                    ${showCancel ? `<button class="btn-cancel" onclick="cancelOrder(${id})">ပယ်ဖျက်မည်</button>` : ''}
                </div>
            </div>
        `;
        
        if (isReady) {
            setTimeout((orderId) => {
                myOrders = myOrders.filter(oid => oid != orderId);
                saveOrdersToStorage();
                updateStatusDisplay();
            }, 60000, id);
        }
    }
    
    statusListEl.innerHTML = html;
    const badgeEl = document.getElementById('order-count-badge');
    if (badgeEl) badgeEl.innerText = myOrders.length;
    updateStatusButton();
}

    window.cancelOrder = async function(id) {
        if (!confirm('ဒီအော်ဒါကို ပယ်ဖျက်မှာ သေချာပါသလား?')) return;
        try {
            const res = await fetch('api/cancel_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            });
            const data = await res.json();
            if (data.success) {
                alert('Order အောင်မြင်စွာ ပယ်ဖျက်ပြီးပါပြီ');
                myOrders = myOrders.filter(oid => oid != id);
                saveOrdersToStorage();
                updateStatusDisplay();
            } else {
                alert(data.message || 'Order ပယ်ဖျက်၍မရပါ');
            }
        } catch(e) {
            alert('Error cancelling order');
        }
    };

    // ==================== INITIALIZATION ====================
    function startLiveTracking() {
        if (statusInterval) clearInterval(statusInterval);
        updateStatusDisplay();
        statusInterval = setInterval(() => updateStatusDisplay(), 5000);
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        loadOrdersFromStorage();
        loadCartFromServer();
        setupEventListeners();
        updateSubCategoryFilterUI();
        applyFilters();
        startLiveTracking();
    });
    
    window.onbeforeunload = function() {
        if (statusInterval) clearInterval(statusInterval);
    };
</script>
</body>
</html>