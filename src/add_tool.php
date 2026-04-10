<?php
// add_tool.php: Handles POST from Add Tool form
require_once 'conn.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['tool_name'];
    $rate = $_POST['rate'];
    $is_retailer = isset($_POST['is_retailer']) ? 1 : 0;
    $stmt = $conn->prepare("INSERT INTO tools (name, rate, is_retailer) VALUES (?, ?, ?)");
    $stmt->bind_param('sdi', $name, $rate, $is_retailer);
    $stmt->execute();
    header('Location: ../public/dashboard.php');
    exit();
}
?>