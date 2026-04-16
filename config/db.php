<?php
$conn = new mysqli("127.0.0.1:3307", "root", "", "srms_db");
if ($conn->connect_error) { die("Database Connection Failed"); }
?>