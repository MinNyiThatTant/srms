<?php
session_start();
include 'config/db.php';

// render to login.php if not login
if (!isset($_SESSION['admin_logged'])) {
    header("Location: login.php");
    exit;
}

// main category - CRUD

// Add Main Category
if (isset($_POST['add_main_category'])) {
    $nm = $conn->real_escape_string($_POST['new_main_cat']);
    $conn->query("INSERT INTO main_categories (name) VALUES ('$nm')");
    header("Location: admin.php?tab=categories");
    exit;
}

// Delete Main Category
if (isset($_GET['del_main'])) {
    $did = (int)$_GET['del_main'];
    $conn->query("DELETE FROM main_categories WHERE id=$did");
    header("Location: admin.php?tab=categories");
    exit;
}

// Edit Main Category
if (isset($_POST['edit_main_category'])) {
    $id = (int)$_POST['main_cat_id'];
    $name = $conn->real_escape_string($_POST['edit_main_cat']);
    $conn->query("UPDATE main_categories SET name='$name' WHERE id=$id");
    header("Location: admin.php?tab=categories");
    exit;
}

// Sub category - CRUD

// Add Sub Category
if (isset($_POST['add_sub_category'])) {
    $main_cat_id = (int)$_POST['main_cat_id'];
    $name = $conn->real_escape_string($_POST['new_sub_cat']);
    $conn->query("INSERT INTO sub_categories (main_category_id, name) VALUES ($main_cat_id, '$name')");
    header("Location: admin.php?tab=categories");
    exit;
}

// Delete Sub Category
if (isset($_GET['del_sub'])) {
    $did = (int)$_GET['del_sub'];
    $conn->query("DELETE FROM sub_categories WHERE id=$did");
    header("Location: admin.php?tab=categories");
    exit;
}

// Edit Sub Category
if (isset($_POST['edit_sub_category'])) {
    $id = (int)$_POST['sub_cat_id'];
    $main_cat_id = (int)$_POST['main_cat_id'];
    $name = $conn->real_escape_string($_POST['edit_sub_cat']);
    $conn->query("UPDATE sub_categories SET main_category_id=$main_cat_id, name='$name' WHERE id=$id");
    header("Location: admin.php?tab=categories");
    exit;
}

// Menu Item - CRUD

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
    $main_cat_id = (int)$_POST['main_cat_id'];
    $sub_cat_id = (int)$_POST['sub_cat_id'];

    if (!empty($_FILES['image']['name'])) {
        $img = time() . '_' . $_FILES['image']['name'];
        if (!is_dir('uploads')) mkdir('uploads');
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $img);
        $conn->query("UPDATE menu SET name='$name', price=$price, main_category_id=$main_cat_id, sub_category_id=$sub_cat_id, image='$img' WHERE id=$id");
    } else {
        $conn->query("UPDATE menu SET name='$name', price=$price, main_category_id=$main_cat_id, sub_category_id=$sub_cat_id WHERE id=$id");
    }
    header("Location: admin.php?tab=menu");
    exit;
}

// Add new menu item
if (isset($_POST['add_item'])) {
    $n = $conn->real_escape_string($_POST['name']);
    $p = (int)$_POST['price'];
    $main_cat_id = (int)$_POST['main_category_id'];
    $sub_cat_id = (int)$_POST['sub_category_id'];
    $img = 'default.jpg';
    if (!empty($_FILES['image']['name'])) {
        $img = time() . '_' . $_FILES['image']['name'];
        if (!is_dir('uploads')) mkdir('uploads');
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $img);
    }
    $conn->query("INSERT INTO menu (name, price, image, main_category_id, sub_category_id) VALUES ('$n', $p, '$img', $main_cat_id, $sub_cat_id)");
    header("Location: admin.php?tab=menu");
    exit;
}

// Delete menu item
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    $conn->query("DELETE FROM menu WHERE id=$id");
    header("Location: admin.php?tab=menu");
    exit;
}

$tab = $_GET['tab'] ?? 'orders';

// Get all main categories for dropdowns
$mainCategories = $conn->query("SELECT * FROM main_categories ORDER BY name");
$mainCatList = [];
while ($row = $mainCategories->fetch_assoc()) {
    $mainCatList[] = $row;
}
$mainCategories->data_seek(0); // Reset pointer
?>
<!DOCTYPE html>
<html lang="my">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
            font-family: 'Segoe UI', sans-serif;
        }

        .nav-tabs .nav-link {
            border: none;
            color: #555;
            padding: 12px 20px;
            font-weight: 600;
        }

        .nav-tabs .nav-link.active {
            background: #0d6efd !important;
            color: white !important;
            border-radius: 10px;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .table img {
            object-fit: cover;
        }

        .category-box {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .sub-cat-item {
            background: white;
            border-radius: 8px;
            padding: 8px 12px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sub-cat-list {
            margin-top: 10px;
            margin-left: 20px;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-dark bg-dark mb-4 shadow-sm">
        <div class="container-fluid px-4 d-flex justify-content-between">
            <span class="navbar-brand fw-bold">ကေတုအလင်္ကာ Smart Restaurant</span>
            <div class="d-flex gap-2">
                <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4">
        <ul class="nav nav-tabs mb-4 border-0 gap-2">
            <li class="nav-item"><a class="nav-link <?= $tab == 'orders' ? 'active' : '' ?>" href="?tab=orders">Order အသစ်များ</a></li>
            <li class="nav-item"><a class="nav-link <?= $tab == 'categories' ? 'active' : '' ?>" href="?tab=categories">Category စီမံရန်</a></li>
            <li class="nav-item"><a class="nav-link <?= $tab == 'menu' ? 'active' : '' ?>" href="?tab=menu">ဟင်းပွဲများ</a></li>
            <li class="nav-item"><a class="nav-link <?= $tab == 'history' ? 'active' : '' ?>" href="?tab=history">ရောင်းအားမှတ်တမ်း</a></li>
        </ul>

        <!-- Orders Tab -->
        <?php if ($tab == 'orders'): ?>
            <div class="card p-3">
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

            <!-- Categories (main, sub) -->
        <?php elseif ($tab == 'categories'): ?>
            <div class="row">
                <!-- Add Main Category -->
                <div class="col-md-5 mb-4">
                    <div class="card p-3">
                        <h6 class="fw-bold text-primary mb-3">Main Category ထည့်ရန်</h6>
                        <form method="POST" class="d-flex gap-2">
                            <input type="text" name="new_main_cat" class="form-control" placeholder="ဥပမာ- အသားဟင်း, ဟင်းချို, အချိုပွဲ" required>
                            <button name="add_main_category" class="btn btn-primary">ထည့်မည်</button>
                        </form>
                    </div>
                </div>

                <!-- Add Sub Category -->
                <div class="col-md-7 mb-4">
                    <div class="card p-3">
                        <h6 class="fw-bold text-success mb-3">Sub Category ထည့်ရန်</h6>
                        <form method="POST" class="row g-2">
                            <div class="col-md-5">
                                <select name="main_cat_id" class="form-select" required>
                                    <option value="">Category ရွေးပါ</option>
                                    <?php
                                    $mcs = $conn->query("SELECT * FROM main_categories ORDER BY name");
                                    while ($mc = $mcs->fetch_assoc()): ?>
                                        <option value="<?= $mc['id'] ?>"><?= htmlspecialchars($mc['name']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <input type="text" name="new_sub_cat" class="form-control" placeholder="ဥပမာ- မြန်မာဟင်း" required>
                            </div>
                            <div class="col-md-2">
                                <button name="add_sub_category" class="btn btn-success w-100">ထည့်မည်</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Display Categories with Subcategories -->
            <div class="row">
                <div class="col-12">
                    <div class="card p-3">
                        <h6 class="fw-bold mb-3">Category များစာရင်း</h6>
                        <?php
                        $mainCats = $conn->query("SELECT * FROM main_categories ORDER BY id DESC");
                        while ($main = $mainCats->fetch_assoc()):
                            $mainId = $main['id'];
                            $subCats = $conn->query("SELECT * FROM sub_categories WHERE main_category_id = $mainId ORDER BY name");
                        ?>
                            <div class="category-box">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold mb-0">
                                        <span class="badge bg-primary">Main</span>
                                        <?= htmlspecialchars($main['name']) ?>
                                    </h6>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-warning btn-sm" onclick="editMainCat(<?= $main['id'] ?>, '<?= htmlspecialchars($main['name']) ?>')">Edit</button>
                                        <a href="?tab=categories&del_main=<?= $main['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('ဖျက်မှာလား?')">Delete</a>
                                    </div>
                                </div>

                                <?php if ($subCats->num_rows > 0): ?>
                                    <div class="sub-cat-list mt-2">
                                        <small class="text-muted">Sub Categories:</small>
                                        <?php while ($sub = $subCats->fetch_assoc()): ?>
                                            <div class="sub-cat-item">
                                                <span>• <?= htmlspecialchars($sub['name']) ?></span>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-warning btn-sm" onclick="editSubCat(<?= $sub['id'] ?>, <?= $main['id'] ?>, '<?= htmlspecialchars($sub['name']) ?>')">Edit</button>
                                                    <a href="?tab=categories&del_sub=<?= $sub['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('ဖျက်မှာလား?')">Delete</a>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-muted small mt-2">No Sub Categories</div>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <!-- Edit Modals -->
            <div class="modal fade" id="editMainCatModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST">
                            <div class="modal-header">
                                <h5 class="modal-title">Main Category ပြင်ရန်</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="main_cat_id" id="edit_main_cat_id">
                                <input type="text" name="edit_main_cat" id="edit_main_cat_name" class="form-control" required>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="edit_main_category" class="btn btn-primary">သိမ်းမည်</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="editSubCatModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST">
                            <div class="modal-header">
                                <h5 class="modal-title">Sub Category ပြင်ရန်</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="sub_cat_id" id="edit_sub_cat_id">
                                <div class="mb-2">
                                    <label>Main Category</label>
                                    <select name="main_cat_id" id="edit_sub_main_cat" class="form-select" required>
                                        <?php
                                        $mcs = $conn->query("SELECT * FROM main_categories ORDER BY name");
                                        while ($mc = $mcs->fetch_assoc()): ?>
                                            <option value="<?= $mc['id'] ?>"><?= htmlspecialchars($mc['name']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div>
                                    <label>Sub Category Name</label>
                                    <input type="text" name="edit_sub_cat" id="edit_sub_cat_name" class="form-control" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="edit_sub_category" class="btn btn-primary">သိမ်းမည်</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Menu Tab -->
        <?php elseif ($tab == 'menu'): ?>
            <?php if ($editData): ?>
                <div class="card p-4 mb-4 border-warning" style="background: #2d3748; color: white;">
                    <h6 class="text-warning fw-bold mb-3">Edit Menu Item</h6>
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
                        <div class="col-md-3">
                            <label class="small text-white-50">Main Category</label>
                            <select name="main_cat_id" class="form-select" id="editMainCatSelect" onchange="loadSubCatsForEdit(this.value)" required>
                                <?php
                                $mcatRes = $conn->query("SELECT * FROM main_categories");
                                while ($mc = $mcatRes->fetch_assoc()):
                                ?>
                                    <option value="<?= $mc['id'] ?>" <?= ($mc['id'] == $editData['main_category_id']) ? 'selected' : '' ?>><?= htmlspecialchars($mc['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="small text-white-50">Sub Category</label>
                            <select name="sub_cat_id" class="form-select" id="editSubCatSelect" required>
                                <option value="">ရွေးပါ</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="small text-white-50">ပုံအသစ်</label>
                            <input type="file" name="image" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <button type="submit" name="update_menu" class="btn btn-success">Update</button>
                            <a href="admin.php?tab=menu" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card p-3">
                        <h6 class="fw-bold">ဟင်းပွဲအသစ်ထည့်ရန်</h6>
                        <form method="POST" enctype="multipart/form-data">
                            <label class="small fw-bold mt-2">Main Category</label>
                            <select name="main_category_id" class="form-control mb-2" id="addMainCatSelect" onchange="loadSubCatsForAdd(this.value)" required>
                                <option value="">ရွေးပါ</option>
                                <?php foreach ($mainCatList as $mc): ?>
                                    <option value="<?= $mc['id'] ?>"><?= htmlspecialchars($mc['name']) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <label class="small fw-bold">Sub Category</label>
                            <select name="sub_category_id" class="form-control mb-2" id="addSubCatSelect" required>
                                <option value="">အရင်ဆုံး Main Category ရွေးပါ</option>
                            </select>

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
                                    <?php
                                    $items = $conn->query("SELECT m.*, mc.name as main_cat_name, sc.name as sub_cat_name 
                                                           FROM menu m 
                                                           LEFT JOIN main_categories mc ON m.main_category_id = mc.id 
                                                           LEFT JOIN sub_categories sc ON m.sub_category_id = sc.id 
                                                           ORDER BY m.id DESC");
                                    while ($row = $items->fetch_assoc()): ?>
                                        <tr>
                                            <td><img src="uploads/<?= $row['image'] ?>" width="40" height="40" class="rounded me-2"><?= $row['name'] ?></td>
                                            <td><small class="badge bg-light text-dark"><?= htmlspecialchars($row['main_cat_name'] ?? '-') ?> / <?= htmlspecialchars($row['sub_cat_name'] ?? '-') ?></small></td>
                                            <td><?= number_format($row['price']) ?> K</td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="admin.php?tab=menu&edit_menu=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                                    <a href="?tab=menu&delete_id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('ဖျက်မလား?')">Delete</a>
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

            <!-- ့History tab -->
        <?php elseif ($tab == 'history'): ?>
    <div class="card p-3">
        <div class="d-flex justify-content-between mb-3 align-items-center">
            <h5 class="fw-bold mb-0">ရောင်းအားမှတ်တမ်း</h5>
            <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">Print Report</button>
        </div>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>Table</th>
                        <th>Items & QTY</th>
                        <th>Total Amount</th>
                        <th>Bill</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Corrected query using LEFT JOIN and GROUP_CONCAT
                    $hist = $conn->query("
                        SELECT o.id, o.table_no, o.total_price, o.created_at,
                            GROUP_CONCAT(CONCAT(od.item_name, '(x', od.cnt, ')') SEPARATOR '<br>') as item_details
                        FROM orders o
                        LEFT JOIN (
                            SELECT order_id, item_name, COUNT(*) as cnt
                            FROM order_details
                            GROUP BY order_id, item_name
                        ) od ON o.id = od.order_id
                        WHERE o.status IN ('ready','delivered')
                        GROUP BY o.id
                        ORDER BY o.id DESC
                        LIMIT 100
                    ");
                    $grand_total = 0;
                    while ($row = $hist->fetch_assoc()):
                        $grand_total += $row['total_price'];
                        $item_details = $row['item_details'] ?? 'ပစ္စည်းမရှိပါ';
                    ?>
                        <tr>
                            <td><?= date('d-M-Y h:i A', strtotime($row['created_at'])) ?></td>
                            <td>Table <?= $row['table_no'] ?></td>
                            <td><?= $item_details ?></td>
                            <td><?= number_format($row['total_price']) ?> K</td>
                            <td><a href="print_receipt.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-sm btn-info text-white">Receipt</a></td>
                        </tr>
                    <?php endwhile; ?>
                    <tr class="table-info fw-bold">
                        <td colspan="3" class="text-end">စုစုပေါင်း ရောင်းအား:</td>
                        <td colspan="2"><?= number_format($grand_total) ?> MMK</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Categories management
        // For Add Menu Form
        function loadSubCatsForAdd(mainCatId) {
            if (!mainCatId) {
                document.getElementById('addSubCatSelect').innerHTML = '<option value="">အရင်ဆုံး Main Category ရွေးပါ</option>';
                return;
            }
            fetch(`api/get_sub_categories.php?main_cat_id=${mainCatId}`)
                .then(res => res.json())
                .then(data => {
                    let html = '<option value="">Sub Category ရွေးပါ</option>';
                    data.forEach(sub => {
                        html += `<option value="${sub.id}">${escapeHtml(sub.name)}</option>`;
                    });
                    document.getElementById('addSubCatSelect').innerHTML = html;
                })
                .catch(err => console.error(err));
        }

        // For Edit Menu Form
        let subCategoriesData = <?php
                                $allSubs = $conn->query("SELECT * FROM sub_categories");
                                $subsArray = [];
                                while ($s = $allSubs->fetch_assoc()) {
                                    $subsArray[$s['main_category_id']][] = ['id' => $s['id'], 'name' => $s['name']];
                                }
                                echo json_encode($subsArray);
                                ?>;

        function loadSubCatsForEdit(mainCatId) {
            const subSelect = document.getElementById('editSubCatSelect');
            const currentSubId = "<?= $editData['sub_category_id'] ?? '' ?>";

            if (!subSelect) return;

            let html = '<option value="">Sub Category ရွေးပါ</option>';
            if (subCategoriesData[mainCatId]) {
                subCategoriesData[mainCatId].forEach(sub => {
                    const selected = (sub.id == currentSubId) ? 'selected' : '';
                    html += `<option value="${sub.id}" ${selected}>${escapeHtml(sub.name)}</option>`;
                });
            }
            subSelect.innerHTML = html;
        }

        // Edit Category Modals        
        function editMainCat(id, name) {
            document.getElementById('edit_main_cat_id').value = id;
            document.getElementById('edit_main_cat_name').value = name;
            new bootstrap.Modal(document.getElementById('editMainCatModal')).show();
        }

        function editSubCat(id, mainCatId, name) {
            document.getElementById('edit_sub_cat_id').value = id;
            document.getElementById('edit_sub_main_cat').value = mainCatId;
            document.getElementById('edit_sub_cat_name').value = name;
            new bootstrap.Modal(document.getElementById('editSubCatModal')).show();
        }

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/[&<>]/g, function(m) {
                if (m === '&') return '&amp;';
                if (m === '<') return '&lt;';
                if (m === '>') return '&gt;';
                return m;
            });
        }

        // Live Orders Fetching and Updating        
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
                            <td><div class='fw-bold'>${o.item_details || 'No items'}</div></td>
                            <td><small>${o.time_formatted}</small></td>
                            <td><button class='btn btn-success btn-sm' onclick='setReady(${o.id})'>Ready</button></td>
                        </tr>`;
                    });
                }
                document.getElementById('live-order-body').innerHTML = html;
            } catch (e) {
                console.error(e);
            }
        }

        async function setReady(id) {
            await fetch(`api/update_status.php?id=${id}&status=ready`);
            fetchLiveOrders();
        }

        // Initialize on load
        window.onload = () => {
            fetchLiveOrders();
            setInterval(fetchLiveOrders, 5000);

            // For edit form - load sub categories
            if (document.getElementById('editMainCatSelect')) {
                const initialMainCat = document.getElementById('editMainCatSelect').value;
                if (initialMainCat) loadSubCatsForEdit(initialMainCat);
            }
        };
    </script>
</body>

</html>