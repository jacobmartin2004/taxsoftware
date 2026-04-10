<?php
require_once '../src/conn.php';

// Company count
$company_count = 0;
$res = $conn->query("SELECT COUNT(*) as cnt FROM companydata");
if ($res) { $row = $res->fetch_assoc(); $company_count = $row['cnt']; }

// Tool count
$tool_count = 0;
$res = $conn->query("SELECT COUNT(*) as cnt FROM tools");
if ($res) { $row = $res->fetch_assoc(); $tool_count = $row['cnt']; }

// Sales this month
$month = date('m');
$year = date('Y');
$sales_count = 0;
$sales_total = 0;
$res = $conn->query("SELECT COUNT(*) as cnt, IFNULL(SUM(Total),0) as total FROM delvin WHERE SUBSTRING(date,4,2)='$month' AND SUBSTRING(date,7,4)='$year'");
if ($res) { $row = $res->fetch_assoc(); $sales_count = $row['cnt']; $sales_total = $row['total']; }

// Purchase this month
$purchase_count = 0;
$purchase_total = 0;
$res = $conn->query("SELECT COUNT(*) as cnt, IFNULL(SUM(Total),0) as total FROM purchase WHERE SUBSTRING(date,4,2)='$month' AND SUBSTRING(date,7,4)='$year'");
if ($res) { $row = $res->fetch_assoc(); $purchase_count = $row['cnt']; $purchase_total = $row['total']; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Delvin Diamond Tools</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f0f0; font-family: Arial, sans-serif; }
        .stat-card { background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; margin-bottom: 20px; }
        .stat-card h3 { color: #333; margin-bottom: 5px; }
        .stat-card .number { font-size: 36px; font-weight: bold; color: #009879; }
        .nav-section a { margin: 5px; }
    </style>
</head>
<body>
<div class="container mt-4">
    <h1 class="text-center mb-1">DELVIN DIAMOND TOOL INDUSTRIES</h1>
    <p class="text-center text-muted">Dashboard</p>
    <hr>

    <div class="row">
        <div class="col-md-3">
            <div class="stat-card">
                <h3>Companies</h3>
                <div class="number"><?php echo $company_count; ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <h3>Tools</h3>
                <div class="number"><?php echo $tool_count; ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <h3>Sales (<?php echo date('M'); ?>)</h3>
                <div class="number"><?php echo $sales_count; ?></div>
                <small>&#8377;<?php echo number_format($sales_total, 2); ?></small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <h3>Purchases (<?php echo date('M'); ?>)</h3>
                <div class="number"><?php echo $purchase_count; ?></div>
                <small>&#8377;<?php echo number_format($purchase_total, 2); ?></small>
            </div>
        </div>
    </div>

    <hr>
    <h4>Quick Links</h4>
    <div class="nav-section">
        <h5>Create</h5>
        <a href="create_invoice.php" class="btn btn-primary">Create Invoice (Sales)</a>
        <a href="create_purchase.php" class="btn btn-info">Create Purchase</a>
        <a href="proforma_invoice.php" class="btn btn-warning">Proforma Invoice</a>
        <a href="quotation.php" class="btn btn-secondary">Quotation</a>
    </div>
    <div class="nav-section mt-3">
        <h5>Records & Reports</h5>
        <a href="../src/view.php" class="btn btn-outline-primary">Sales Record</a>
        <a href="view1.php" class="btn btn-outline-info">Purchase Record</a>
        <a href="../src/printsales.php" class="btn btn-outline-success">Print Sales Report</a>
        <a href="../src/printpurchase.php" class="btn btn-outline-success">Print Purchase Report</a>
    </div>
    <div class="nav-section mt-3">
        <h5>Manage</h5>
        <a href="../src/companydata.php" class="btn btn-outline-dark">Company Data</a>
        <a href="add_tool.php" class="btn btn-outline-dark">Manage Tools</a>
    </div>
    <div class="nav-section mt-3">
        <h5>Old Pages</h5>
        <a href="index.php" class="btn btn-outline-secondary btn-sm">Old Sales Entry</a>
        <a href="../src/purchase.php" class="btn btn-outline-secondary btn-sm">Old Purchase Entry</a>
    </div>
</div>
</body>
</html>