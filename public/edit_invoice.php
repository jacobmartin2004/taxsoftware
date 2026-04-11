<?php
require_once '../src/auth.php';
require_once '../src/conn.php';

$type = isset($_GET['type']) ? $_GET['type'] : 'sales';
$bill_param = isset($_GET['bill']) ? intval($_GET['bill']) : 0;

if ($bill_param <= 0) { header('Location: ' . ($type === 'purchase' ? 'view_purchases.php' : 'view_invoices.php')); exit(); }

$table = ($type === 'purchase') ? 'purchase' : 'delvin';

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_bill = intval($_POST['old_bill']);
    $gstno = strtoupper(trim($_POST['gstno']));
    $cname = strtoupper(trim($_POST['cname']));
    $bill = intval($_POST['bill']);
    $taxamt = floatval($_POST['taxamt']);
    $cgst = floatval($_POST['cgst']);
    $sgst = floatval($_POST['sgst']);
    $igst = floatval($_POST['igst']);
    $total = intval($_POST['total']);
    $date = $_POST['date'];

    $stmt = $conn->prepare("UPDATE `$table` SET GSTNO=?, cname=?, bill=?, taxamt=?, cgst=?, sgst=?, igst=?, Total=?, date=? WHERE bill=?");
    $stmt->bind_param('ssiddddisi', $gstno, $cname, $bill, $taxamt, $cgst, $sgst, $igst, $total, $date, $old_bill);
    if ($stmt->execute()) {
        header("Location: " . ($type === 'purchase' ? 'view_purchases.php' : 'view_invoices.php'));
        exit();
    } else {
        $error = $conn->error;
    }
}

// Fetch invoice
$stmt = $conn->prepare("SELECT GSTNO, cname, bill, taxamt, cgst, sgst, igst, Total, date FROM `$table` WHERE bill = ?");
$stmt->bind_param('i', $bill_param);
$stmt->execute();
$result = $stmt->get_result();
$inv = $result->fetch_assoc();
$stmt->close();

if (!$inv) { echo "<p style='padding:40px;font-family:sans-serif;'>Invoice not found.</p>"; exit(); }

$page_title = ($type === 'purchase') ? 'Edit Purchase Invoice' : 'Edit Sales Invoice';

// Determine if TNGST or IGST
$is_tngst = ($inv['cgst'] > 0 || $inv['sgst'] > 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
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
        .edit-box { max-width: 800px; margin: 24px auto; padding: 24px; background: #fff; border-radius: 10px; border: 1px solid #e2e8f0; }
        .edit-box h3 { color: var(--primary); font-weight: 700; margin-bottom: 20px; }
        .totals-section { background: #e9ecef; padding: 15px; border-radius: 5px; margin-top: 15px; }
        .total-final { font-size: 20px; font-weight: bold; }
        @media (max-width: 768px) {
            .top-nav { flex-wrap: wrap; padding: 10px 16px; }
            .top-nav .menu-toggle { display: block; }
            .top-nav .nav-links { display: none; width: 100%; flex-direction: column; padding-top: 10px; }
            .top-nav .nav-links.show { display: flex; }
            .top-nav .nav-links a { padding: 10px 14px; border-bottom: 1px solid rgba(255,255,255,0.1); }
            .edit-box { margin: 10px; padding: 14px; }
            .form-control, .form-select { font-size: 16px; }
            .btn-lg { width: 100%; margin-bottom: 8px; }
        }
    </style>
</head>
<body>
<nav class="top-nav">
    <span class="brand">DELVIN DIAMOND TOOLS</span>
    <button class="menu-toggle" onclick="this.nextElementSibling.classList.toggle('show')"><i class="bi bi-list"></i></button>
    <div class="nav-links">
        <a href="../index.php"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
        <a href="create_invoice.php"><i class="bi bi-receipt"></i> Invoice</a>
        <a href="create_purchase.php"><i class="bi bi-cart-plus"></i> Purchase</a>
        <a href="view_invoices.php"><i class="bi bi-graph-up-arrow"></i> Sales Invoices</a>
        <a href="view_purchases.php"><i class="bi bi-cart-check"></i> Purchase Invoices</a>
        <a href="../src/companydata.php"><i class="bi bi-building"></i> Companies</a>
        <a href="add_tool.php"><i class="bi bi-tools"></i> Tools</a>
        <a href="../logout.php" style="color:#ef4444;"><i class="bi bi-box-arrow-left"></i> Logout</a>
    </div>
</nav>
<div class="edit-box">
    <h3><i class="bi bi-pencil-square me-2"></i><?php echo $page_title; ?></h3>
    <?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="hidden" name="old_bill" value="<?php echo $inv['bill']; ?>">
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label fw-bold">Company Name:</label>
                <input type="text" class="form-control" name="cname" value="<?php echo htmlspecialchars($inv['cname']); ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Bill No:</label>
                <input type="number" class="form-control" name="bill" value="<?php echo htmlspecialchars($inv['bill']); ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Date:</label>
                <input type="text" class="form-control" name="date" value="<?php echo htmlspecialchars($inv['date']); ?>" required>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label fw-bold">GST No:</label>
                <input type="text" class="form-control" name="gstno" value="<?php echo htmlspecialchars($inv['GSTNO']); ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Taxable Amount (₹):</label>
                <input type="number" class="form-control" name="taxamt" id="taxamt" step="0.01" value="<?php echo $inv['taxamt']; ?>" onchange="recalculate()" onkeyup="recalculate()" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Tax Type:</label>
                <select class="form-select" id="tax_type" onchange="recalculate()">
                    <option value="tngst" <?php echo $is_tngst ? 'selected' : ''; ?>>CGST + SGST</option>
                    <option value="igst" <?php echo !$is_tngst ? 'selected' : ''; ?>>IGST</option>
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label">CGST (₹):</label>
                <input type="number" class="form-control" name="cgst" id="cgst" step="0.01" value="<?php echo $inv['cgst']; ?>" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label">SGST (₹):</label>
                <input type="number" class="form-control" name="sgst" id="sgst" step="0.01" value="<?php echo $inv['sgst']; ?>" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label">IGST (₹):</label>
                <input type="number" class="form-control" name="igst" id="igst" step="0.01" value="<?php echo $inv['igst']; ?>" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label">Total (₹):</label>
                <input type="number" class="form-control fw-bold" name="total" id="total" value="<?php echo $inv['Total']; ?>" readonly>
            </div>
        </div>
        <div class="mt-3 text-center">
            <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-lg me-1"></i>Update Invoice</button>
            <a href="view_invoices.php?type=<?php echo htmlspecialchars($type); ?>" class="btn btn-secondary btn-lg">Cancel</a>
        </div>
    </form>
</div>
<script>
function recalculate() {
    var taxable = parseFloat(document.getElementById('taxamt').value) || 0;
    var taxType = document.getElementById('tax_type').value;
    var cgst = 0, sgst = 0, igst = 0;
    if (taxType === 'tngst') {
        cgst = taxable * 0.09;
        sgst = taxable * 0.09;
    } else {
        igst = taxable * 0.18;
    }
    var total = Math.round(taxable + cgst + sgst + igst);
    document.getElementById('cgst').value = cgst.toFixed(1);
    document.getElementById('sgst').value = sgst.toFixed(1);
    document.getElementById('igst').value = igst.toFixed(1);
    document.getElementById('total').value = total;
}
</script>
</body>
</html>
