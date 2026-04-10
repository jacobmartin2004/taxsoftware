<?php
// add_company.php: Handles POST from Add Company form
require_once 'conn.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['company_name'];
    $address = $_POST['address'];
    $state = $_POST['state'];
    $district = $_POST['district'];
    $gst_no = $_POST['gst_no'];
    $stmt = $conn->prepare("INSERT INTO companies (name, address, state, district, gst_no) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('sssss', $name, $address, $state, $district, $gst_no);
    $stmt->execute();
    header('Location: ../public/dashboard.php');
    exit();
}
?>