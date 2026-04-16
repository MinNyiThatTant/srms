<?php 
session_start();
include 'config/db.php';

if(!isset($_SESSION['admin_logged'])) { header("Location: login.php"); exit; } // Login ရှိပြီးသားလို့ ယူဆပါတယ်

// --- CRUD LOGIC ---
// ၁။ Add Item
if(isset($_POST['add_item'])) {
    $n = $_POST['name']; $p = $_POST['price'];
    $conn->query("INSERT INTO menu (name, price) VALUES ('$n', $p)");
}

// ၂။ Update Item
if(isset($_POST['update_item'])) {
    $id = $_POST['id']; $n = $_POST['name']; $p = $_POST['price'];
    $conn->query("UPDATE menu SET name='$n', price=$p WHERE id=$id");
    header("Location: admin.php?tab=menu"); exit;
}

// ၃။ Delete Item
if(isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $conn->query("DELETE FROM menu WHERE id=$id");
    header("Location: admin.php?tab=menu"); exit;
}

$tab = $_GET['tab'] ?? 'orders'; // Default tab
?>

<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <title>Admin - Master Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .nav-tabs .nav-link { color: #555; fw-bold; }
        .nav-tabs .nav-link.active { background: #6366f1; color: white; border: none; }
        .table-v { vertical-align: middle; }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4 shadow-sm">
    <div class="container-fluid px-4">
        <span class="navbar-brand fw-bold">🚀 Smart Restaurant Admin</span>
        <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
    </div>
</nav>

<div class="container-fluid px-4">
    <ul class="nav nav-tabs mb-4 border-0">
        <li class="nav-item">
            <a class="nav-link <?= $tab == 'orders' ? 'active' : '' ?>" href="?tab=orders">Active Orders</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab == 'history' ? 'active' : '' ?>" href="?tab=history">Order History (Records)</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab == 'menu' ? 'active' : '' ?>" href="?tab=menu">Menu Management (CRUD)</a>
        </li>
    </ul>

    <?php if($tab == 'orders'): ?>
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold">အခုချက်ချင်း ပြင်ဆင်ရမည့် အော်ဒါများ</div>
            <div class="table-responsive">
                <table class="table table-hover table-v mb-0">
                    <thead class="table-light"><tr><th>Table</th><th>Items</th><th>Time</th><th>Action</th></tr></thead>
                    <tbody id="live-order-body">
                        <?php 
                        $res = $conn->query("SELECT * FROM orders WHERE status='pending' ORDER BY id DESC");
                        while($o = $res->fetch_assoc()): ?>
                        <tr>
                            <td><span class="badge bg-primary">Table <?= $o['table_no'] ?></span></td>
                            <td>
                                <?php 
                                $details = $conn->query("SELECT item_name, count(*) as qty FROM order_details WHERE order_id=".$o['id']." GROUP BY item_name");
                                while($d = $details->fetch_assoc()) echo "• ".$d['item_name']." (x".$d['qty'].")<br>";
                                ?>
                            </td>
                            <td><?= date('h:i A', strtotime($o['created_at'])) ?></td>
                            <td><button class="btn btn-success btn-sm" onclick="setReady(<?= $o['id'] ?>)">Ready</button></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php elseif($tab == 'history'): ?>
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold">ရောင်းအားမှတ်တမ်းများ</span>
                <form class="d-flex gap-2">
                    <input type="hidden" name="tab" value="history">
                    <input type="date" name="date" class="form-control form-control-sm" value="<?= $_GET['date'] ?? date('Y-m-d') ?>">
                    <button class="btn btn-dark btn-sm">Filter</button>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead><tr><th>Order ID</th><th>Table</th><th>Details</th><th>Total</th><th>Time</th></tr></thead>
                    <tbody>
                        <?php 
                        $filterDate = $_GET['date'] ?? date('Y-m-d');
                        $res = $conn->query("SELECT * FROM orders WHERE DATE(created_at) = '$filterDate' ORDER BY created_at DESC");
                        $totalDayRevenue = 0;
                        while($o = $res->fetch_assoc()): 
                            $totalDayRevenue += $o['total_price'];
                        ?>
                        <tr>
                            <td>#<?= $o['id'] ?></td>
                            <td>Table <?= $o['table_no'] ?></td>
                            <td>
                                <?php 
                                $details = $conn->query("SELECT item_name, count(*) as qty FROM order_details WHERE order_id=".$o['id']." GROUP BY item_name");
                                while($d = $details->fetch_assoc()) echo $d['item_name']." (".$d['qty']."), ";
                                ?>
                            </td>
                            <td><?= number_format($o['total_price']) ?> K</td>
                            <td><?= date('h:i A', strtotime($o['created_at'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot class="table-dark">
                        <tr><td colspan="3" class="text-end">Total Daily Sales:</td><td colspan="2"><?= number_format($totalDayRevenue) ?> MMK</td></tr>
                    </tfoot>
                </table>
            </div>
        </div>

    <?php elseif($tab == 'menu'): ?>
        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-dark text-white fw-bold">ဟင်းပွဲ အသစ်ထည့်/ပြင်ရန်</div>
                    <div class="card-body">
                        <?php 
                        $editData = ['id'=>'', 'name'=>'', 'price'=>''];
                        if(isset($_GET['edit_id'])){
                            $eid = $_GET['edit_id'];
                            $editData = $conn->query("SELECT * FROM menu WHERE id=$eid")->fetch_assoc();
                        }
                        ?>
                        <form method="POST">
                            <input type="hidden" name="id" value="<?= $editData['id'] ?>">
                            <label class="small">ဟင်းပွဲအမည်</label>
                            <input type="text" name="name" class="form-control mb-2" value="<?= $editData['name'] ?>" required>
                            <label class="small">ဈေးနှုန်း</label>
                            <input type="number" name="price" class="form-control mb-3" value="<?= $editData['price'] ?>" required>
                            
                            <?php if(isset($_GET['edit_id'])): ?>
                                <button name="update_item" class="btn btn-warning w-100 mb-2">Update Item</button>
                                <a href="?tab=menu" class="btn btn-light w-100 border">Cancel</a>
                            <?php else: ?>
                                <button name="add_item" class="btn btn-primary w-100">Add to Menu</button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead><tr><th>Name</th><th>Price</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php 
                                $res = $conn->query("SELECT * FROM menu ORDER BY id DESC");
                                while($row = $res->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['name'] ?></td>
                                    <td><?= number_format($row['price']) ?> K</td>
                                    <td>
                                        <a href="?tab=menu&edit_id=<?= $row['id'] ?>" class="btn btn-outline-info btn-sm">Edit</a>
                                        <a href="?delete_id=<?= $row['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('ဖျက်မှာလား?')">Delete</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    function setReady(id) {
        fetch(`api/update_status.php?id=${id}&status=ready`).then(() => location.reload());
    }

    // အော်ဒါတွေကို API ကနေ ဆွဲထုတ်ပြီး Table ထဲ ထည့်မယ့် function
    async function fetchLiveOrders() {
        try {
            const res = await fetch('api/get_live_orders.php');
            const orders = await res.json();
            
            let html = "";
            if(orders.length === 0) {
                html = "<tr><td colspan='4' class='text-center text-muted'>အော်ဒါအသစ် မရှိသေးပါ...</td></tr>";
            } else {
                orders.forEach(o => {
                    html += `
                    <tr>
                        <td><span class="badge bg-primary">Table ${o.table_no}</span></td>
                        <td>${o.item_details}</td>
                        <td>${o.time_formatted}</td>
                        <td>
                            <button class="btn btn-success btn-sm" onclick="setReady(${o.id})">Ready</button>
                        </td>
                    </tr>`;
                });
            }
            
            // Table content ကို refresh မလုပ်ဘဲ update လုပ်မယ်
            document.getElementById('live-order-body').innerHTML = html;
        } catch (err) {
            console.error("Error fetching orders:", err);
        }
    }

    // Ready ခလုတ်နှိပ်ရင် အလုပ်လုပ်မယ့် function
    async function setReady(id) {
        if(confirm('အော်ဒါ ပြင်ဆင်ပြီးပြီလား?')) {
            await fetch(`api/update_status.php?id=${id}&status=ready`);
            fetchLiveOrders(); // Status ပြောင်းပြီးတာနဲ့ ချက်ချင်း list ကို update လုပ်မယ်
        }
    }

    // Page စဖွင့်တာနဲ့ အော်ဒါတွေကို စဆွဲမယ်
    fetchLiveOrders();

    // ၃ စက္ကန့်တစ်ခါ အော်ဒါအသစ် ရှိမရှိ အလိုအလျောက် စစ်နေမယ် (Live ဖြစ်အောင်)
    setInterval(fetchLiveOrders, 3000);
</script>
</body>
</html>