<?php
// Database connection (replace with your actual database credentials)
include('conn.php');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch GST number and GST type based on company name
if (isset($_POST['companyname'])) {
    $companyname = $_POST['companyname'];

    $sql = "SELECT gstno, gsttype FROM companydata WHERE companyname = '$companyname'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Return both gstno and gsttype as a JSON response
        echo json_encode([
            'gstno' => $row['gstno'],
            'gsttype' => $row['gsttype']
        ]);
    } else {
        echo json_encode([
            'gstno' => '',
            'gsttype' => ''
        ]);
    }
}

$conn->close();
?>
