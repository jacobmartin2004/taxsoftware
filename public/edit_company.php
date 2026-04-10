<?php
require_once '../src/conn.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$company = null;
if ($id) {
    $stmt = $conn->prepare("SELECT * FROM companydata WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $company = $stmt->get_result()->fetch_assoc();
}
if (!$company) { echo "Company not found."; exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $companyname = $_POST['companyname'];
    $gstno = $_POST['gstno'];
    $gsttype = $_POST['gsttype'];
    $address = $_POST['address'];
    $state = $_POST['state'];
    $district = $_POST['district'];
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("UPDATE companydata SET companyname=?, gstno=?, gsttype=?, address=?, state=?, district=? WHERE id=?");
    $stmt->bind_param('ssssssi', $companyname, $gstno, $gsttype, $address, $state, $district, $id);
    $stmt->execute();
    header('Location: ../src/companydata.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Company - Delvin Diamond Tools</title>
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
            .form-control, .form-select { font-size: 16px; }
            .btn { font-size: 16px; }
        }
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
        <a href="../src/companydata.php" class="active"><i class="bi bi-building"></i> Companies</a>
        <a href="add_tool.php"><i class="bi bi-tools"></i> Tools</a>
    </div>
</nav>
<div class="container mt-4">
<h2>Edit Company</h2>
<form method="post">
    <input type="hidden" name="id" value="<?php echo $company['id']; ?>">
    <div class="mb-3">
        <label class="form-label">Company Name:</label>
        <input type="text" class="form-control" name="companyname" value="<?php echo htmlspecialchars($company['companyname']); ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">GST No:</label>
        <input type="text" class="form-control" name="gstno" value="<?php echo htmlspecialchars($company['gstno']); ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">GST Type:</label>
        <select class="form-control" name="gsttype" required>
            <option value="tngst" <?php if($company['gsttype']=='tngst') echo 'selected'; ?>>TNGST (Tamil Nadu)</option>
            <option value="igst" <?php if($company['gsttype']=='igst') echo 'selected'; ?>>IGST (Other State)</option>
            <option value="25p" <?php if($company['gsttype']=='25p') echo 'selected'; ?>>25p</option>
            <option value="6p" <?php if($company['gsttype']=='6p') echo 'selected'; ?>>6p</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Address:</label>
        <textarea class="form-control" name="address" rows="2" required><?php echo htmlspecialchars($company['address'] ?? ''); ?></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">State:</label>
        <input type="text" class="form-control" name="state" value="<?php echo htmlspecialchars($company['state'] ?? ''); ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">District:</label>
        <input type="text" class="form-control" name="district" value="<?php echo htmlspecialchars($company['district'] ?? ''); ?>" required>
    </div>
    <button type="submit" class="btn btn-primary">Update Company</button>
    <a href="../src/companydata.php" class="btn btn-secondary">Cancel</a>
</form>
</div>
</body>
</html>