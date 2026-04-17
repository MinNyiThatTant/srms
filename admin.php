<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['admin_logged'])) {
    header("Location: login.php");
    exit;
}

// --- CRUD LOGIC ---
if (isset($_POST['add_item'])) {
    $n = $_POST['name'];
    $p = $_POST['price'];
    $img = $_POST['image'] ?? 'default.jpg';
    $cat = $_POST['category'] ?? 'cool drink';


    if ($_FILES['image']['name']) {
        $img = time() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $img);
    }
    $conn->query("INSERT INTO menu (name, price, image, category) VALUES ('$n', $p, '$img', '$cat')");
}

if (isset($_POST['update_item'])) {
    $id = $_POST['id'];
    $n = $_POST['name'];
    $p = $_POST['price'];
    $cat = $_POST['category'] ?? 'cool drink';
    $conn->query("UPDATE menu SET name='$n', price=$p, category='$cat' WHERE id=$id");
    header("Location: admin.php?tab=menu");
    exit;
}

if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $conn->query("DELETE FROM menu WHERE id=$id");
    header("Location: admin.php?tab=menu");
    exit;
}

$tab = $_GET['tab'] ?? 'orders';
?>

<!DOCTYPE html>
<html lang="my">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .nav-tabs .nav-link {
            color: #555;
            font-weight: bold;
            border: none;
        }

        .nav-tabs .nav-link.active {
            background: #6366f1;
            color: white;
            border-radius: 8px;
        }

        .table-v {
            vertical-align: middle;
        }

        .btn-print {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
        }
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
        <ul class="nav nav-tabs mb-4 border-0 gap-2">
            <li class="nav-item"><a class="nav-link <?= $tab == 'orders' ? 'active' : '' ?>" href="?tab=orders">Active Orders</a></li>
            <li class="nav-item"><a class="nav-link <?= $tab == 'history' ? 'active' : '' ?>" href="?tab=history">Order History</a></li>
            <li class="nav-item"><a class="nav-link <?= $tab == 'menu' ? 'active' : '' ?>" href="?tab=menu">Menu Management</a></li>
        </ul>

        <?php if ($tab == 'orders'): ?>
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">အခုချက်ချင်း ပြင်ဆင်ရမည့် အော်ဒါများ</div>
                <div class="table-responsive">
                    <table class="table table-hover table-v mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Table</th>
                                <th>Items</th>
                                <th>Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="live-order-body">
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($tab == 'history'): ?>
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
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Table</th>
                                <th>Details</th>
                                <th>Total</th>
                                <th>Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $filterDate = $_GET['date'] ?? date('Y-m-d');
                            $res = $conn->query("SELECT * FROM orders WHERE DATE(created_at) = '$filterDate' ORDER BY created_at DESC");
                            $totalDayRevenue = 0;
                            while ($o = $res->fetch_assoc()):
                                $totalDayRevenue += $o['total_price'];
                            ?>
                                <tr>
                                    <td>#<?= $o['id'] ?></td>
                                    <td>Table <?= $o['table_no'] ?></td>
                                    <td>
                                        <?php
                                        $details = $conn->query("SELECT item_name, count(*) as qty FROM order_details WHERE order_id=" . $o['id'] . " GROUP BY item_name");
                                        while ($d = $details->fetch_assoc()) echo $d['item_name'] . " (" . $d['qty'] . "), ";
                                        ?>
                                    </td>
                                    <td class="fw-bold"><?= number_format($o['total_price']) ?> K</td>
                                    <td><?= date('h:i A', strtotime($o['created_at'])) ?></td>
                                    <td>
                                        <button class="btn btn-print btn-sm" onclick="printBill(<?= $o['id'] ?>)">🖨️ Bill</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot class="table-dark">
                            <tr>
                                <td colspan="3" class="text-end">Total Daily Sales:</td>
                                <td colspan="3"><?= number_format($totalDayRevenue) ?> MMK</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        <?php elseif ($tab == 'menu'): ?>
            <div class="row">
                <div class="col-md-4">
                    <div class="card shadow-sm mb-4 border-0">
                        <div class="card-header bg-dark text-white fw-bold">ဟင်းပွဲ အသစ်ထည့်/ပြင်ရန်</div>
                        <div class="card-body">
                            <?php
                            $editData = ['id' => '', 'name' => '', 'price' => '', 'category' => 'coll drink'];
                            if (isset($_GET['edit_id'])) {
                                $eid = $_GET['edit_id'];
                                $editData = $conn->query("SELECT * FROM menu WHERE id=$eid")->fetch_assoc();
                            }
                            ?>
                            <form method="POST" enctype="multipart/form-data">
                                <label class="small fw-bold">အမျိုးအစား</label>
                                <select name="category" class="form-control mb-2">
                                    <option value="cool drink">Cool Drink</option>
                                    <option value="hot drink">Hot Drink</option>
                                    <option value="traditional food">Traditional Food</option>
                                    <option value="thai food">Thai Food</option>
                                    <option value="lunch">Lunch</option>
                                    <option value="dinner">Dinner</option>
                                    <option value="dessert">Dessert</option>
                                </select>
                                <input type="file" name="image" class="form-control mb-3">
                                <label class="small fw-bold">ဟင်းပွဲပုံ</label>
                                <input type="hidden" name="id" value="<?= $editData['id'] ?>">
                                <label class="small fw-bold">ဟင်းပွဲအမည်</label>
                                <input type="text" name="name" class="form-control mb-2" value="<?= $editData['name'] ?>" required>
                                <label class="small fw-bold">ဈေးနှုန်း</label>
                                <input type="number" name="price" class="form-control mb-3" value="<?= $editData['price'] ?>" required>
                                <?php if (isset($_GET['edit_id'])): ?>
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
                    <div class="card shadow-sm border-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Price</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $res = $conn->query("SELECT * FROM menu ORDER BY id DESC");
                                    while ($row = $res->fetch_assoc()): ?>
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
        // အော်ဒါတွေကို API ကနေ ဆွဲထုတ်ပြီး Table ထဲ ထည့်မယ် (Live Update)
        async function fetchLiveOrders() {
            if ("<?= $tab ?>" !== 'orders') return; // Active orders tab မှာမှ အလုပ်လုပ်မယ်

            try {
                const res = await fetch('api/get_live_orders.php');
                const orders = await res.json();

                let html = "";
                if (orders.length === 0) {
                    html = "<tr><td colspan='4' class='text-center text-muted'>အော်ဒါအသစ် မရှိသေးပါ...</td></tr>";
                } else {
                    orders.forEach(o => {
                        html += `
                        <tr>
                            <td><span class="badge bg-primary">Table ${o.table_no}</span></td>
                            <td>${o.item_details}</td>
                            <td>${o.time_formatted}</td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-success btn-sm" onclick="setReady(${o.id})">Ready</button>
                                    <button class="btn btn-print btn-sm" onclick="printBill(${o.id})">🖨️ Bill</button>
                                </div>
                            </td>
                        </tr>`;
                    });
                }
                document.getElementById('live-order-body').innerHTML = html;
            } catch (err) {
                console.error("Error fetching orders:", err);
            }
        }

        async function setReady(id) {
            if (confirm('အော်ဒါ ပြင်ဆင်ပြီးပြီလား?')) {
                await fetch(`api/update_status.php?id=${id}&status=ready`);
                fetchLiveOrders();
            }
        }

        function printBill(orderId) {
            const width = 400;
            const height = 600;
            const left = (screen.width / 2) - (width / 2);
            const top = (screen.height / 2) - (height / 2);
            window.open(`print_receipt.php?id=${orderId}`, 'PrintReceipt', `width=${width},height=${height},top=${top},left=${left}`);
        }

        // Live Fetch စတင်ခြင်း
        fetchLiveOrders();
        setInterval(fetchLiveOrders, 3000);
    </script>
</body>

</html>