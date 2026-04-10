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
</head>
<body>
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