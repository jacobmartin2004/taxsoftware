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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --primary: #1a2942; --accent: #e8a838; }
        body { background: #f1f5f9; font-family: 'Segoe UI', system-ui, sans-serif; }
        .top-nav { background: var(--primary); padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; position: relative; }
        .top-nav a { color: rgba(255,255,255,0.8); text-decoration: none; font-size: 14px; padding: 6px 14px; border-radius: 6px; transition: all 0.2s; }
        .top-nav a:hover, .top-nav a.active { background: rgba(255,255,255,0.1); color: #fff; }
        .top-nav .brand { color: var(--accent); font-weight: 700; font-size: 15px; }
        .top-nav .nav-links { display: flex; gap: 4px; flex-wrap: wrap; }
        .top-nav .menu-toggle { display: none; background: none; border: none; color: #fff; font-size: 24px; cursor: pointer; }
        @media (max-width: 768px) {
            .top-nav { flex-wrap: wrap; padding: 10px 16px; }
            .top-nav .menu-toggle { display: block; }
            .top-nav .nav-links { display: none; width: 100%; flex-direction: column; padding-top: 10px; }
            .top-nav .nav-links.show { display: flex; }
            .top-nav .nav-links a { padding: 10px 14px; border-bottom: 1px solid rgba(255,255,255,0.1); }
            .form-box { margin: 10px !important; padding: 14px !important; }
            .form-control, .form-select { font-size: 16px; }
            .btn { font-size: 16px; }
        }
        .page-wrap { max-width: 900px; margin: 24px auto; padding: 0 16px; }
        .card-box { background: #fff; border-radius: 10px; border: 1px solid #e2e8f0; padding: 24px; margin-bottom: 20px; }
        .search-box { position: relative; margin-bottom: 16px; }
        .search-box input { width: 100%; padding: 10px 16px 10px 40px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; }
        .search-box i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
    </style>
</head>
<body>
<nav class="top-nav">
    <span class="brand">DELVIN DIAMOND TOOLS</span>
    <button class="menu-toggle" onclick="this.nextElementSibling.classList.toggle('show')"><i class="bi bi-list"></i></button>
    <div class="nav-links">
        <a href="index.php"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
        <a href="create_invoice.php"><i class="bi bi-receipt"></i> Invoice</a>
        <a href="create_purchase.php"><i class="bi bi-cart-plus"></i> Purchase</a>
        <a href="view_invoices.php"><i class="bi bi-journal-bookmark-fill"></i> Invoices</a>
        <a href="../src/companydata.php"><i class="bi bi-building"></i> Companies</a>
        <a href="add_tool.php" class="active"><i class="bi bi-tools"></i> Tools</a>
    </div>
</nav>
<div class="page-wrap">
    <div class="card-box">
        <h4 class="mb-3"><i class="bi bi-plus-circle me-2"></i>Add New Tool</h4>
        <form method="POST">
            <div class="row">
                <div class="col-md-5 mb-3">
                    <label for="toolname" class="form-label fw-bold">Tool Name:</label>
                    <input type="text" class="form-control" id="toolname" name="toolname" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="rate" class="form-label fw-bold">Rate (₹):</label>
                    <input type="number" class="form-control" id="rate" name="rate" step="0.01" required>
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100" name="add_tool"><i class="bi bi-plus-lg me-1"></i>Add Tool</button>
                </div>
            </div>
        </form>
    </div>
    <div class="card-box">
        <h4 class="mb-3"><i class="bi bi-list-ul me-2"></i>Tools List</h4>
        <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" id="toolSearch" placeholder="Search tools by name..." onkeyup="filterTools()">
        </div>
        <table class="table table-striped" id="toolsTable">
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
                    echo "<a href='edit_tool.php?id=" . $row['id'] . "' class='btn btn-warning btn-sm'><i class='bi bi-pencil'></i> Edit</a> ";
                    echo "<form method='POST' style='display:inline'><input type='hidden' name='id' value='" . $row['id'] . "'><button type='submit' name='delete_tool' class='btn btn-danger btn-sm'><i class='bi bi-trash'></i> Delete</button></form>";
                    echo "</td></tr>";
                }
            } else {
                echo "<tr><td colspan='4' class='text-center text-muted'>No tools found</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
<script>
function filterTools() {
    var query = document.getElementById('toolSearch').value.toLowerCase();
    var rows = document.querySelectorAll('#toolsTable tbody tr');
    rows.forEach(function(row) {
        var name = row.cells[1] ? row.cells[1].textContent.toLowerCase() : '';
        row.style.display = name.indexOf(query) > -1 ? '' : 'none';
    });
}
</script>
</body>
</html>