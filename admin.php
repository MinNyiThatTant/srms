<?php
session_start();
include 'config/db.php';

// render to login.php if not login
if (!isset($_SESSION['admin_logged'])) {
    header("Location: login.php");
    exit;
}

// --- CRUD  ---

// take data for editing
$editData = null;
if (isset($_GET['edit_menu'])) {
    $eid = (int)$_GET['edit_menu'];
    $res = $conn->query("SELECT * FROM menu WHERE id = $eid");
    $editData = $res->fetch_assoc();
}

// Menu Update 
if (isset($_POST['update_menu'])) {
    $id = (int)$_POST['menu_id'];
    $name = $conn->real_escape_string($_POST['name']);
    $price = (int)$_POST['price'];
    $main_cat = $conn->real_escape_string($_POST['main_cat']);
    $sub_cat = $conn->real_escape_string($_POST['sub_cat']);

    if (!empty($_FILES['image']['name'])) {
        $img = time() . '_' . $_FILES['image']['name'];
        if (!is_dir('uploads')) mkdir('uploads');
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $img);
        $conn->query("UPDATE menu SET name='$name', price=$price, main_category='$main_cat', category='$sub_cat', image='$img' WHERE id=$id");
    } else {
        $conn->query("UPDATE menu SET name='$name', price=$price, main_category='$main_cat', category='$sub_cat' WHERE id=$id");
    }
    header("Location: admin.php?tab=menu");
    exit;
}

// Main Category 
if (isset($_POST['add_main_category'])) {
    $nm = $conn->real_escape_string($_POST['new_main_cat']);
    $conn->query("INSERT INTO main_categories (name) VALUES ('$nm')");
}

// Main Category delete
if (isset($_GET['del_main'])) {
    $did = (int)$_GET['del_main'];
    $conn->query("DELETE FROM main_categories WHERE id=$did");
    header("Location: admin.php?tab=categories");
    exit;
}

// Add Sub Category 
if (isset($_POST['add_category'])) {
    $m = $conn->real_escape_string($_POST['main_cat']);
    $s = $conn->real_escape_string($_POST['sub_cat_name']);
    $conn->query("INSERT INTO categories (main_category, sub_category_name) VALUES ('$m', '$s')");
}

// Sub Category delete
if (isset($_GET['del_cat'])) {
    $did = (int)$_GET['del_cat'];
    $conn->query("DELETE FROM categories WHERE id=$did");
    header("Location: admin.php?tab=categories");
    exit;
}

// add new items(category)
if (isset($_POST['add_item'])) {
    $n = $conn->real_escape_string($_POST['name']);
    $p = (int)$_POST['price'];
    $main_cat = $conn->real_escape_string($_POST['main_category']);
    $cat = $conn->real_escape_string($_POST['category']);
    $img = 'default.jpg';
    if (!empty($_FILES['image']['name'])) {
        $img = time() . '_' . $_FILES['image']['name'];
        if (!is_dir('uploads')) mkdir('uploads');
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $img);
    }
    $conn->query("INSERT INTO menu (name, price, image, category, main_category) VALUES ('$n', $p, '$img', '$cat', '$main_cat')");
}

// delete
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .nav-tabs .nav-link { border: none; color: #555; padding: 12px 20px; font-weight: 600; }
        .nav-tabs .nav-link.active { background: #0d6efd !important; color: white !important; border-radius: 10px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .table img { object-fit: cover; }
    </style>
</head>
<body>

    <nav class="navbar navbar-dark bg-dark mb-4 shadow-sm">
        <div class="container-fluid px-4">
            <span class="navbar-brand fw-bold"> Smart ကေတုအလင်္ကာ Restaurant</span>
            <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container-fluid px-4">
        <ul class="nav nav-tabs mb-4 border-0 gap-2">
            <li class="nav-item"><a class="nav-link <?= $tab == 'orders' ? 'active' : '' ?>" href="?tab=orders"> Order အသစ်များ</a></li>
            <li class="nav-item"><a class="nav-link <?= $tab == 'categories' ? 'active' : '' ?>" href="?tab=categories">Category စီမံရန်</a></li>
            <li class="nav-item"><a class="nav-link <?= $tab == 'menu' ? 'active' : '' ?>" href="?tab=menu">ဟင်းပွဲများ</a></li>
            <li class="nav-item"><a class="nav-link <?= $tab == 'history' ? 'active' : '' ?>" href="?tab=history">ရောင်းအားမှတ်တမ်း</a></li>
        </ul>

        <?php if ($tab == 'orders'): ?>
            <div class="card p-3">
                <h5 class="fw-bold mb-3">Live Orders</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Table</th>
                                <th>Items</th>
                                <th>Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="live-order-body"></tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($tab == 'categories'): ?>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card p-3">
                        <h6 class="fw-bold">Main Category ပေါင်းရန်</h6>
                        <form method="POST" class="d-flex gap-2 mb-3">
                            <input type="text" name="new_main_cat" class="form-control" placeholder="ဥပမာ- အစားအသောက်" required>
                            <button name="add_main_category" class="btn btn-primary">ထည့်မည်</button>
                        </form>
                        <ul class="list-group shadow-sm">
                            <?php $mc = $conn->query("SELECT * FROM main_categories");
                            while ($r = $mc->fetch_assoc()): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= $r['name'] ?>
                                    <a href="?tab=categories&del_main=<?= $r['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('ဖျက်မှာလား?')">&times;</a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card p-3">
                        <h6 class="fw-bold">Sub Category ပေါင်းရန်</h6>
                        <form method="POST">
                            <select name="main_cat" class="form-control mb-2" required>
                                <?php $mc = $conn->query("SELECT * FROM main_categories");
                                while ($r = $mc->fetch_assoc()) echo "<option value='{$r['name']}'>{$r['name']}</option>"; ?>
                            </select>
                            <div class="d-flex gap-2">
                                <input type="text" name="sub_cat_name" class="form-control" placeholder="ဥပမာ- ခေါက်ဆွဲ" required>
                                <button name="add_category" class="btn btn-dark">ထည့်မည်</button>
                            </div>
                        </form>
                        <hr>
                        <ul class="list-group shadow-sm">
                            <?php $sc = $conn->query("SELECT * FROM categories");
                            while ($r = $sc->fetch_assoc()): ?>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><?= $r['sub_category_name'] ?> <small class="text-muted">(<?= $r['main_category'] ?>)</small></span>
                                    <a href="?tab=categories&del_cat=<?= $r['id'] ?>" class="text-danger fw-bold" style="text-decoration:none;">&times;</a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                </div>
            </div>

        <?php elseif ($tab == 'menu'): ?>
            <?php if ($editData): ?>
                <div class="card p-4 mb-4 border-warning" style="background: #2d3748; color: white;">
                    <h6 class="text-warning fw-bold mb-3">📝 Edit Menu Item</h6>
                    <form method="POST" enctype="multipart/form-data" class="row g-3">
                        <input type="hidden" name="menu_id" value="<?= $editData['id'] ?>">
                        <div class="col-md-3">
                            <label class="small text-white-50">အမည်</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($editData['name']) ?>" required>
                        </div>
                        <div class="col-md-2">
                            <label class="small text-white-50">ဈေးနှုန်း</label>
                            <input type="number" name="price" class="form-control" value="<?= $editData['price'] ?>" required>
                        </div>
                        <div class="col-md-2">
                            <label class="small text-white-50">Main Category</label>
                            <select name="main_cat" class="form-select" id="mainCatSelect" onchange="updateSubCategories()" required>
                                <?php
                                $mcats = $conn->query("SELECT * FROM main_categories");
                                while ($mc = $mcats->fetch_assoc()):
                                ?>
                                    <option value="<?= $mc['name'] ?>" <?= ($mc['name'] == $editData['main_category']) ? 'selected' : '' ?>><?= $mc['name'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="small text-white-50">Sub Category</label>
                            <select name="sub_cat" class="form-select" id="subCatSelect" required></select>
                        </div>
                        <div class="col-md-2">
                            <label class="small text-white-50">ပုံအသစ် (Optional)</label>
                            <input type="file" name="image" class="form-control">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" name="update_menu" class="btn btn-success w-100">Update</button>
                        </div>
                        <div class="col-12"><a href="admin.php?tab=menu" class="text-white-50 small">Cancel Edit</a></div>
                    </form>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card p-3">
                        <h6 class="fw-bold">ဟင်းပွဲအသစ်ထည့်ရန်</h6>
                        <form method="POST" enctype="multipart/form-data">
                            <label class="small fw-bold mt-2">Main Category</label>
                            <select name="main_category" class="form-control mb-2" id="mSel" onchange="updateSub()">
                                <?php $mc = $conn->query("SELECT * FROM main_categories");
                                while ($r = $mc->fetch_assoc()) echo "<option value='{$r['name']}'>{$r['name']}</option>"; ?>
                            </select>
                            <label class="small fw-bold">Sub Category</label>
                            <select name="category" class="form-control mb-2" id="sSel"></select>
                            <label class="small fw-bold">အမည်</label>
                            <input type="text" name="name" class="form-control mb-2" required>
                            <label class="small fw-bold">ဈေးနှုန်း</label>
                            <input type="number" name="price" class="form-control mb-2" required>
                            <label class="small fw-bold">ပုံတင်ရန်</label>
                            <input type="file" name="image" class="form-control mb-3">
                            <button name="add_item" class="btn btn-primary w-100">Add Item</button>
                        </form>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card p-3">
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>ဟင်းပွဲ</th>
                                        <th>အမျိုးအစား</th>
                                        <th>ဈေးနှုန်း</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $items = $conn->query("SELECT * FROM menu ORDER BY id DESC");
                                    while ($row = $items->fetch_assoc()): ?>
                                        <tr>
                                            <td><img src="uploads/<?= $row['image'] ?>" width="40" height="40" class="rounded me-2"><?= $row['name'] ?></td>
                                            <td><small class="badge bg-light text-dark"><?= $row['main_category'] ?> / <?= $row['category'] ?></small></td>
                                            <td><?= number_format($row['price']) ?> K</td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="admin.php?tab=menu&edit_menu=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                                    <a href="?tab=menu&delete_id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('ဖျက်မလား?')">Del</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($tab == 'history'): ?>
            <div class="card p-3">
                <div class="d-flex justify-content-between mb-3 align-items-center">
                    <h5 class="fw-bold mb-0">ရောင်းအားမှတ်တမ်း (Completed)</h5>
                    <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">Print Report</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Date</th>
                                <th>Table</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $hist = $conn->query("SELECT * FROM orders WHERE status IN ('ready','delivered') ORDER BY id DESC LIMIT 100");
                            $grand_total = 0;
                            while ($row = $hist->fetch_assoc()):
                                $grand_total += $row['total_price'];
                            ?>
                                <tr>
                                    <td><?= date('d-M-Y h:i A', strtotime($row['created_at'])) ?></td>
                                    <td>Table <?= $row['table_no'] ?></td>
                                    <td><?= number_format($row['total_price']) ?> K</td>
                                    <td><span class="badge bg-success">Paid</span></td>
                                </tr>
                            <?php endwhile; ?>
                            <tr class="table-info fw-bold">
                                <td colspan="2" class="text-end">စုစုပေါင်း ရောင်းအား:</td>
                                <td colspan="2"><?= number_format($grand_total) ?> MMK</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Live Orders fetching
        async function fetchLiveOrders() {
            if ("<?= $tab ?>" !== 'orders') return;
            try {
                const res = await fetch('api/get_live_orders.php');
                const orders = await res.json();
                let html = "";
                if (orders.length === 0) {
                    html = "<tr><td colspan='4' class='text-center py-4 text-muted'>ယနေ့အတွက် အော်ဒါအသစ်မရှိသေးပါ</td></tr>";
                } else {
                    orders.forEach(o => {
                        html += `<tr>
                            <td><span class='badge bg-primary fs-6'>Table ${o.table_no}</span></td>
                            <td><div class='fw-bold'>${o.item_details}</div></td>
                            <td><small>${o.time_formatted}</small></td>
                            <td>
                                <button class='btn btn-success btn-sm' onclick='setReady(${o.id})'>Ready</button>
                                <button class='btn btn-outline-secondary btn-sm' onclick="window.open('print_receipt.php?id=${o.id}','_blank','width=400')">Bill</button>
                            </td>
                        </tr>`;
                    });
                }
                document.getElementById('live-order-body').innerHTML = html;
            } catch (e) { console.error(e); }
        }

        async function setReady(id) {
            await fetch(`api/update_status.php?id=${id}&status=ready`);
            fetchLiveOrders();
        }

        // Sub-category grouping data
        const catData = <?php
            $all = $conn->query("SELECT * FROM categories");
            $data = [];
            while ($r = $all->fetch_assoc()) $data[$r['main_category']][] = $r['sub_category_name'];
            echo json_encode($data);
        ?>;

        function updateSub() {
            const m = document.getElementById('mSel').value;
            const s = document.getElementById('sSel');
            s.innerHTML = "";
            if (catData[m]) {
                catData[m].forEach(sub => {
                    let opt = document.createElement('option');
                    opt.value = opt.innerText = sub;
                    s.appendChild(opt);
                });
            }
        }

        function updateSubCategories() {
            const mainCatSelect = document.getElementById('mainCatSelect');
            if(!mainCatSelect) return;
            const mainCat = mainCatSelect.value;
            const subCatSelect = document.getElementById('subCatSelect');
            const selectedSub = "<?= $editData['category'] ?? '' ?>";

            subCatSelect.innerHTML = '';
            if (catData[mainCat]) {
                catData[mainCat].forEach(sub => {
                    let opt = document.createElement('option');
                    opt.value = sub;
                    opt.innerText = sub;
                    if (sub === selectedSub) opt.selected = true;
                    subCatSelect.appendChild(opt);
                });
            }
        }

        // Initialize
        setInterval(fetchLiveOrders, 5000);
        window.onload = () => {
            fetchLiveOrders();
            if (document.getElementById('mSel')) updateSub();
            if (document.getElementById('mainCatSelect')) updateSubCategories();
        };
    </script>
</body>
</html>