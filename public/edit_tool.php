<?php
require_once '../src/conn.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$tool = null;
if ($id) {
    $stmt = $conn->prepare("SELECT * FROM tools WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $tool = $stmt->get_result()->fetch_assoc();
}
if (!$tool) { echo "Tool not found."; exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $toolname = $_POST['toolname'];
    $rate = $_POST['rate'];
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("UPDATE tools SET toolname=?, rate=? WHERE id=?");
    $stmt->bind_param('sdi', $toolname, $rate, $id);
    $stmt->execute();
    header('Location: add_tool.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Tool - Delvin Diamond Tools</title>
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
        <a href="../index.php"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
        <a href="create_invoice.php"><i class="bi bi-receipt"></i> Invoice</a>
        <a href="create_purchase.php"><i class="bi bi-cart-plus"></i> Purchase</a>
        <a href="view_invoices.php"><i class="bi bi-graph-up-arrow"></i> Sales Invoices</a>
        <a href="view_purchases.php"><i class="bi bi-cart-check"></i> Purchase Invoices</a>
        <a href="../src/companydata.php"><i class="bi bi-building"></i> Companies</a>
        <a href="add_tool.php" class="active"><i class="bi bi-tools"></i> Tools</a>
    </div>
</nav>
<div class="container mt-4">
<h2>Edit Tool</h2>
<form method="post">
    <input type="hidden" name="id" value="<?php echo $tool['id']; ?>">
    <div class="mb-3">
        <label class="form-label">Tool Name:</label>
        <input type="text" class="form-control" name="toolname" value="<?php echo htmlspecialchars($tool['toolname']); ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Rate (₹):</label>
        <input type="number" class="form-control" name="rate" step="0.01" value="<?php echo $tool['rate']; ?>" required>
    </div>
    <button type="submit" class="btn btn-primary">Update Tool</button>
    <a href="add_tool.php" class="btn btn-secondary">Cancel</a>
</form>
</body>
</html>