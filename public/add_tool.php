<?php
include("../src/conn.php");

// Handle add tool
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_tool'])) {
    $toolname = $_POST['toolname'];
    $rate = $_POST['rate'];
    $stmt = $conn->prepare("INSERT INTO tools (toolname, rate) VALUES (?, ?)");
    $stmt->bind_param('sd', $toolname, $rate);
    if ($stmt->execute()) {
        echo '<div class="alert alert-success">Tool added successfully!</div>';
    } else {
        echo '<div class="alert alert-danger">Error: ' . $conn->error . '</div>';
    }
}

// Handle delete
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_tool'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM tools WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    header("Location: add_tool.php");
    exit();
}

$tools = $conn->query("SELECT * FROM tools ORDER BY toolname");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Tool - Delvin Diamond Tools</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Add Tool</h2>
    <form method="POST" class="mb-4">
        <div class="mb-3">
            <label for="toolname" class="form-label">Tool Name:</label>
            <input type="text" class="form-control" id="toolname" name="toolname" required>
        </div>
        <div class="mb-3">
            <label for="rate" class="form-label">Rate (₹):</label>
            <input type="number" class="form-control" id="rate" name="rate" step="0.01" required>
        </div>
        <button type="submit" class="btn btn-primary" name="add_tool">Add Tool</button>
    </form>
    <hr>
    <h3>Tools List</h3>
    <table class="table table-striped">
        <thead>
            <tr><th>S.No</th><th>Tool Name</th><th>Rate (₹)</th><th>Action</th></tr>
        </thead>
        <tbody>
        <?php
        $sno = 1;
        if ($tools && $tools->num_rows > 0) {
            while ($row = $tools->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $sno++ . "</td>";
                echo "<td>" . htmlspecialchars($row['toolname']) . "</td>";
                echo "<td>" . number_format($row['rate'], 2) . "</td>";
                echo "<td>";
                echo "<a href='edit_tool.php?id=" . $row['id'] . "' class='btn btn-warning btn-sm'>Edit</a> ";
                echo "<form method='POST' style='display:inline'><input type='hidden' name='id' value='" . $row['id'] . "'><button type='submit' name='delete_tool' class='btn btn-danger btn-sm'>Delete</button></form>";
                echo "</td></tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No tools found</td></tr>";
        }
        ?>
        </tbody>
    </table>
    <a href="index.php" class="btn btn-outline-primary">Dashboard</a>
    <a href="index.php" class="btn btn-outline-secondary">Home</a>
</div>
</body>
</html>