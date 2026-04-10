<?php
require_once '../src/conn.php';
$id = $_GET['id'] ?? null;
if ($id) {
    $res = $conn->query("SELECT * FROM companies WHERE id=$id");
    $company = $res->fetch_assoc();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['company_name'];
    $address = $_POST['address'];
    $state = $_POST['state'];
    $district = $_POST['district'];
    $gst_no = $_POST['gst_no'];
    $id = $_POST['id'];
    $stmt = $conn->prepare("UPDATE companies SET name=?, address=?, state=?, district=?, gst_no=? WHERE id=?");
    $stmt->bind_param('sssssi', $name, $address, $state, $district, $gst_no, $id);
    $stmt->execute();
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head><title>Edit Company</title></head>
<body>
<h1>Edit Company</h1>
<form method="post">
    <input type="hidden" name="id" value="<?php echo $company['id']; ?>">
    <label>Company Name: <input type="text" name="company_name" value="<?php echo htmlspecialchars($company['name']); ?>" required></label><br>
    <label>Address: <input type="text" name="address" value="<?php echo htmlspecialchars($company['address']); ?>" required></label><br>
    <label>State: <input type="text" name="state" value="<?php echo htmlspecialchars($company['state']); ?>" required></label><br>
    <label>District: <input type="text" name="district" value="<?php echo htmlspecialchars($company['district']); ?>" required></label><br>
    <label>GST No: <input type="text" name="gst_no" value="<?php echo htmlspecialchars($company['gst_no']); ?>" required></label><br>
    <button type="submit">Update Company</button>
</form>
</body>
</html>