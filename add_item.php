<?php 
include 'config/db.php'; 
if(isset($_POST['add'])) {
    $name = $_POST['name']; $price = $_POST['price']; $cat = $_POST['cat'];
    $conn->query("INSERT INTO menu (name, price, category) VALUES ('$name', '$price', '$cat')");
    echo "<script>alert('Added!'); window.location='add_item.php';</script>";
}
?>
<!DOCTYPE html>
<html>
<body>
    <h2>Add New Menu Item</h2>
    <form method="POST">
        <input type="text" name="name" placeholder="Item Name" required><br><br>
        <input type="number" name="price" placeholder="Price" required><br><br>
        <input type="text" name="cat" placeholder="Category (Food/Drink)"><br><br>
        <button type="submit" name="add">Add to Menu</button>
    </form>
    <hr>
    <a href="admin.php">Go to Dashboard</a>
</body>
</html>