<?php
include '../config/db.php';
header('Content-Type: application/json');

$main_cat_id = isset($_GET['main_cat_id']) ? (int)$_GET['main_cat_id'] : 0;

if ($main_cat_id > 0) {
    $result = $conn->query("SELECT id, name FROM sub_categories WHERE main_category_id = $main_cat_id ORDER BY name");
    $subs = [];
    while ($row = $result->fetch_assoc()) {
        $subs[] = $row;
    }
    echo json_encode($subs);
} else {
    echo json_encode([]);
}
?>