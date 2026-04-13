<?php
require_once 'auth.php';
include('conn.php');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['companyname'])) {
    $companyname = $_POST['companyname'];
    $stmt = $conn->prepare("SELECT gstno, gsttype, address FROM companydata WHERE companyname = ?");
    $stmt->bind_param('s', $companyname);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            'gstno' => $row['gstno'],
            'gsttype' => $row['gsttype'],
            'address' => $row['address'] ?? ''
        ]);
    } else {
        echo json_encode([
            'gstno' => '',
            'gsttype' => '',
            'address' => ''
        ]);
    }
}

$conn->close();
?>
