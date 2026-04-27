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

        .btn-add {
            background: #6366f1;
            color: white;
            border: none;
            width: 100%;
            border-radius: 10px;
            font-weight: bold;
            padding: 8px;
        }

        /* Floating Status Tracker */
        /* .status-tracker {
            position: fixed;
            top: 15px;
            right: 15px;
            width: 280px;
            background: #1e293b;
            border: 1px solid #6366f1;
            border-radius: 15px;
            padding: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: none;
        } */

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

        .btn-sub {
            border-radius: 20px;
            white-space: nowrap;
            font-size: 12px;
            margin-right: 5px;
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

        /* Status Tracker */
        .status-tracker {
            position: fixed;
            top: 80px;
            /* Toggle button */
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

        /* for phone responsive */
        @media (max-width: 576px) {
            .status-tracker {
                width: calc(100% - 40px);
                right: 20px;
                left: 20px;
            }
        }

        /* adjust image */
        .menu-img {
            width: 100%;
            height: 180px;
            /* constant image's height */
            object-fit: cover;
            /* image fit */
            border-bottom: 1px solid #2e344e;
        }

        /* Menu Card */
        .menu-card {
            background: #1e293b;
            border: 1px solid #2e344e;
            border-radius: 20px;
            transition: 0.3s;
            overflow: hidden;
            height: 100%;
        }

        /* name of item */
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

        /* Add Button, Hover Effect */
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

        /* Hover effect for light */
        .btn-add:hover {
            background: #4f46e5;
            color: #ffffff !important;
            /* text transform */
            transform: scale(1.02);
            /* text scale (big) */
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
        $name = $mc['name']; // name in Database
    ?>
        <li class="nav-item">
            <button class="nav-link <?= ($c == 0) ? 'active' : '' ?>"
                onclick="filterMainCat('<?= $name ?>', this)">
                <?= htmlspecialchars($mc['name']) ?>
            </button>
        </li>
    <?php $c++; endwhile; ?>
</ul>

        <!-- <div class="category-scroll d-flex overflow-auto pb-2 mb-3" id="subCatContainer"></div> -->

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

                    <button class="btn-add" onclick="addToCart('<?= addslashes($row['name']) ?>', <?= $row['price'] ?>)">
                        Add +
                    </button>
                </div>
        </div>
    </div>
<?php endwhile; ?>
</div>
</div>

<div class="cart-bar shadow" onclick="placeOrder()">
    🛒 Confirm Order (<span id="cart-count">0</span>) - <span id="cart-total">0</span> K
</div>

<script>
    let cart = [];
    // myOrderIDs
    let myOrderIDs = JSON.parse(localStorage.getItem('myOrders')) || [];
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
        if (cart.length === 0) return alert("ဟင်းပွဲအရင်ရွေးပါ");
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
            myOrderIDs.push(result.order_id);
            localStorage.setItem('myOrders', JSON.stringify(myOrderIDs));
            alert("Order တင်ပြီးပါပြီ!");
            cart = [];
            location.reload();
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

        // Show order items, if order done
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

                    let currentStatus = data.status.toLowerCase();
                    let isPending = (currentStatus === 'pending');
                    let isReady = (currentStatus === 'ready');

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
                        // off, if ready order list, done
                        setTimeout(() => {
                            myOrderIDs = myOrderIDs.filter(oid => oid != id);
                            localStorage.setItem('myOrders', JSON.stringify(myOrderIDs));
                        }, 60000);
                    }
                } catch (e) {}
            }

            document.getElementById('status-list').innerHTML = html;
            document.getElementById('order-count-badge').innerText = activeOrders;

            if (activeOrders === 0) {
                document.getElementById('status-toggle').style.display = 'none';
                document.getElementById('status-tracker').style.display = 'none';
            }
        }, 5000);
    }

    window.onload = () => {
        const firstTab = document.querySelector('#mainCatTabs .nav-link.active');
        if (firstTab) firstTab.click();
        startLiveTracking();
    };

    async function cancelOrder(id) {
        if (!confirm('ဒီအော်ဒါကို ပယ်ဖျက်မှာ သေချာပါသလား?')) return;

        try {
            const res = await fetch('api/cancel_order.php', { // cancel order path
                method: 'POST',
                body: JSON.stringify({
                    id: id
                }),
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            const data = await res.json();

            if (data.success) {
                alert('Order cancelled successfully');
                location.reload(); // delete with page refresh
            } else {
                alert(data.message); // done order , it has done ready
            }
        } catch (e) {
            console.error(e);
            alert('Error cancelling order');
        }
    }

    function filterMainCat(mainCat, element) {
    // remove tab active class
    document.querySelectorAll('#mainCatTabs .nav-link').forEach(link => {
        link.classList.remove('active');
        link.style.background = 'transparent';
    });

    // change color tab
    element.classList.add('active');
    element.style.background = '#6366f1';

    // check menu items all
    const cards = document.querySelectorAll('.menu-item-card');

    cards.forEach(card => {
        const cardCat = card.getAttribute('data-main-cat');
        
        // show name both are same (db and category card)
        if (mainCat === 'all' || cardCat === mainCat) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>
</body>

</html>