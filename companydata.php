<?php
// Database connection (replace with your actual database credentials)
include("conn.php");

// Handle form submission for adding a new record
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_record'])) {
    // Get form data
    $companyname = $_POST['companyname'];
    $gstno = $_POST['GSTno'];
    $gsttype = $_POST['gsttype'];

    // Insert data into the companydata table
    $sql = "INSERT INTO companydata (companyname, gstno, gsttype) VALUES ('$companyname', '$gstno', '$gsttype')";

    if ($conn->query($sql) === TRUE) {
        echo '<div class="alert alert-success" role="alert">New record created successfully!</div>';
    } else {
        echo '<div class="alert alert-danger" role="alert">Error: ' . $sql . '<br>' . $conn->error . '</div>';
    }
}

// Handle delete request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_record'])) {
    $id = $_POST['id'];

    // Delete record from the companydata table
    $sql = "DELETE FROM companydata WHERE id='$id'";

    if ($conn->query($sql) === TRUE) {
        echo '<div class="alert alert-success" role="alert">Record deleted successfully!</div>';
    } else {
        echo '<div class="alert alert-danger" role="alert">Error: ' . $sql . '<br>' . $conn->error . '</div>';
    }
}

// Fetch data from companydata table
$sql = "SELECT * FROM companydata";
$result = $conn->query($sql);

// Close connection at the end of the page to keep it open during rendering
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Data Form</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2 class="mb-4">Enter Company Data</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="companyname">Company Name:</label>
                <input type="text" class="form-control" id="companyname" name="companyname" required>
            </div>

            <div class="form-group">
                <label for="GSTno">GST No:</label>
                <input type="text" class="form-control" id="GSTno" name="GSTno" required>
            </div>

            <div class="form-group">
                <label for="gsttype">GST Type:</label>
                <select class="form-control" id="gsttype" name="gsttype" required>
                    <option value="tngst">TNGST</option>
                    <option value="igst">IGST</option>
                    <option value="25p">25p</option>
                    <option value="6p">6p</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" name="add_record">Submit</button>
        </form>

        <hr>

        <h2 class="mt-5">Company Data</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Company Name</th>
                    <th>GST No</th>
                    <th>GST Type</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    $si_no = 1; // Initialize the serial number counter
                    // Output data of each row
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $si_no . "</td>"; // Display the serial number
                        echo "<td>" . $row['companyname'] . "</td>";
                        echo "<td>" . $row['gstno'] . "</td>";
                        echo "<td>" . $row['gsttype'] . "</td>";
                        echo "<td>";
                        echo "<form method='POST' action='' style='display:inline-block;'>";
                        echo "<input type='hidden' name='id' value='" . $row['id'] . "'>";
                        echo "<button type='submit' class='btn btn-danger' name='delete_record'>Delete</button>";
                        echo "</form>";
                        echo "</td>";
                        echo "</tr>";
                        $si_no++; // Increment the serial number for the next row
                    }
                } else {
                    echo "<tr><td colspan='5'>No data found</td></tr>"; // Adjust colspan to account for the extra SI No. column
                }
                ?>

            </tbody>
        </table>
    </div>
    <center>
    <a href="index.php" class="btn btn-outline-primary">Home</a><br><br>
    </center>
    <!-- Include Bootstrap JS (optional, for advanced functionality) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
// Close the database connection after page load
$conn->close();
?>