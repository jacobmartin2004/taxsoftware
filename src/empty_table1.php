<?php
// Include connection file
include("conn.php");

// SQL query to truncate the purchase table
$sql_empty = "TRUNCATE TABLE delvin";

if ($conn->query($sql_empty) === TRUE) {
    echo "Table 'purchase' has been emptied successfully.";
} else {
    echo "Error emptying table: " . $conn->error;
}

// Close connection
$conn->close();

// Redirect back to the main page after operation
header("Location: index.php");
exit;
?>
