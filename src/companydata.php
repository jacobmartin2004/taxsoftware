<?php
require_once 'auth.php';
include("conn.php");

// Handle form submission for adding a new record
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_record'])) {
    $companyname = $_POST['companyname'];
    $gstno = $_POST['GSTno'];
    $gsttype = $_POST['gsttype'];
    $address = $_POST['address'];

    $stmt = $conn->prepare("INSERT INTO companydata (companyname, gstno, gsttype, address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $companyname, $gstno, $gsttype, $address);
    if ($stmt->execute()) {
        $msg = '<div class="alert alert-success alert-dismissible fade show" role="alert">New record created successfully!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    } else {
        $msg = '<div class="alert alert-danger alert-dismissible fade show" role="alert">Error: ' . htmlspecialchars($conn->error) . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
}

// Handle delete request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_record'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM companydata WHERE id=?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $msg = '<div class="alert alert-success alert-dismissible fade show" role="alert">Record deleted successfully!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    } else {
        $msg = '<div class="alert alert-danger alert-dismissible fade show" role="alert">Error: ' . htmlspecialchars($conn->error) . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
}

// Fetch data from companydata table
$sql = "SELECT * FROM companydata ORDER BY companyname";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Companies - Delvin Diamond Tools</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --primary: #1a2942; --accent: #e8a838; --bg: #f1f5f9; }
        body { background: var(--bg); font-family: 'Segoe UI', system-ui, sans-serif; color: #1e293b; }
        .top-nav { background: var(--primary); padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; position: relative; }
        .top-nav a { color: rgba(255,255,255,0.8); text-decoration: none; font-size: 14px; padding: 6px 14px; border-radius: 6px; transition: all 0.2s; }
        .top-nav a:hover, .top-nav a.active { background: rgba(255,255,255,0.1); color: #fff; }
        .top-nav .brand { color: var(--accent); font-weight: 700; font-size: 15px; }
        .top-nav .nav-links { display: flex; gap: 4px; flex-wrap: wrap; }
        .top-nav .menu-toggle { display: none; background: none; border: none; color: #fff; font-size: 24px; cursor: pointer; }
        .page-wrap { max-width: 1100px; margin: 24px auto; padding: 0 16px; }
        .card-box { background: #fff; border-radius: 10px; border: 1px solid #e2e8f0; padding: 24px; margin-bottom: 20px; }
        .card-box h4 { font-weight: 700; color: var(--primary); margin-bottom: 20px; }
        .table thead th { background: var(--primary); color: #fff; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
        .table tbody td { vertical-align: middle; font-size: 14px; }
        .table tbody tr:hover { background: #f8fafc; }
        .search-box { position: relative; margin-bottom: 16px; }
        .search-box input { width: 100%; padding: 10px 16px 10px 40px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; }
        .search-box i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
        .badge-tngst { background: #dbeafe; color: #1d4ed8; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .badge-igst { background: #fef3c7; color: #b45309; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        @media (max-width: 768px) {
            .top-nav { flex-wrap: wrap; padding: 10px 16px; }
            .top-nav .menu-toggle { display: block; }
            .top-nav .nav-links { display: none; width: 100%; flex-direction: column; padding-top: 10px; }
            .top-nav .nav-links.show { display: flex; }
            .top-nav .nav-links a { padding: 10px 14px; border-bottom: 1px solid rgba(255,255,255,0.1); }
            .page-wrap { padding: 0 8px; margin: 12px auto; }
            .card-box { padding: 14px; }
            .table { font-size: 12px; }
            .table thead th, .table tbody td { padding: 8px 6px; }
            .form-control, .form-select { font-size: 16px; }
            .btn { width: 100%; margin-bottom: 8px; }
        }
    </style>
</head>

<body>
    <nav class="top-nav">
        <span class="brand">DELVIN DIAMOND TOOLS</span>
        <button class="menu-toggle" onclick="this.nextElementSibling.classList.toggle('show')"><i class="bi bi-list"></i></button>
        <div class="nav-links">
            <a href="../index.php"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
            <a href="../public/create_invoice.php"><i class="bi bi-receipt"></i> Invoice</a>
            <a href="../public/create_purchase.php"><i class="bi bi-cart-plus"></i> Purchase</a>
            <a href="../public/view_invoices.php"><i class="bi bi-graph-up-arrow"></i> Sales Invoices</a>
            <a href="../public/view_purchases.php"><i class="bi bi-cart-check"></i> Purchase Invoices</a>
            <a href="companydata.php" class="active"><i class="bi bi-building"></i> Companies</a>
            <a href="../public/add_tool.php"><i class="bi bi-tools"></i> Tools</a>
        </div>
    </nav>

    <div class="page-wrap">
        <?php if (isset($msg)) echo $msg; ?>

        <div class="card-box">
            <h4><i class="bi bi-building-add me-2"></i>Add New Company</h4>
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="companyname" class="form-label fw-bold">Company Name:</label>
                        <input type="text" class="form-control" id="companyname" name="companyname" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="GSTno" class="form-label fw-bold">GST No:</label>
                        <input type="text" class="form-control" id="GSTno" name="GSTno" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="gsttype" class="form-label fw-bold">GST Type:</label>
                        <select class="form-select" id="gsttype" name="gsttype" required>
                            <option value="tngst">TNGST (Tamil Nadu)</option>
                            <option value="igst">IGST (Other State)</option>
                            <option value="25p">25p</option>
                            <option value="6p">6p</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="address" class="form-label fw-bold">Address:</label>
                        <input type="text" class="form-control" id="address" name="address" placeholder="Full address including district, state" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" name="add_record"><i class="bi bi-plus-lg me-1"></i>Add Company</button>
            </form>
        </div>

        <div class="card-box">
            <h4><i class="bi bi-list-ul me-2"></i>Company List</h4>
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="companySearch" placeholder="Search by company name or GST no..." onkeyup="filterCompanies()">
            </div>
            <div class="table-responsive">
                <table class="table table-hover" id="companyTable">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Company Name</th>
                            <th>GST No</th>
                            <th>Type</th>
                            <th>Address</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            $si_no = 1;
                            while ($row = $result->fetch_assoc()) {
                                $badge_class = ($row['gsttype'] === 'tngst') ? 'badge-tngst' : 'badge-igst';
                                echo "<tr>";
                                echo "<td>" . $si_no . "</td>";
                                echo "<td><strong>" . htmlspecialchars($row['companyname']) . "</strong></td>";
                                echo "<td><small>" . htmlspecialchars($row['gstno']) . "</small></td>";
                                echo "<td><span class='" . $badge_class . "'>" . strtoupper(htmlspecialchars($row['gsttype'])) . "</span></td>";
                                echo "<td>" . htmlspecialchars($row['address'] ?? '') . "</td>";
                                echo "<td>";
                                echo "<a href='../public/edit_company.php?id=" . $row['id'] . "' class='btn btn-warning btn-sm me-1'><i class='bi bi-pencil'></i></a>";
                                echo "<form method='POST' action='' style='display:inline-block;'>";
                                echo "<input type='hidden' name='id' value='" . $row['id'] . "'>";
                                echo "<button type='submit' class='btn btn-danger btn-sm' name='delete_record' onclick='return confirm(\"Delete this company?\")'><i class='bi bi-trash'></i></button>";
                                echo "</form>";
                                echo "</td>";
                                echo "</tr>";
                                $si_no++;
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center text-muted py-4'>No companies found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function filterCompanies() {
        var query = document.getElementById('companySearch').value.toLowerCase();
        var rows = document.querySelectorAll('#companyTable tbody tr');
        rows.forEach(function(row) {
            var name = row.cells[1] ? row.cells[1].textContent.toLowerCase() : '';
            var gst = row.cells[2] ? row.cells[2].textContent.toLowerCase() : '';
            row.style.display = (name.indexOf(query) > -1 || gst.indexOf(query) > -1) ? '' : 'none';
        });
    }
    </script>
</body>

</html>

<?php
$conn->close();
?>