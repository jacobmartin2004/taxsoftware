<?php
require_once '../src/conn.php';
$id = $_GET['id'] ?? null;
if ($id) {
    $res = $conn->query("SELECT * FROM tools WHERE id=$id");
    $tool = $res->fetch_assoc();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['tool_name'];
    $rate = $_POST['rate'];
    $is_retailer = isset($_POST['is_retailer']) ? 1 : 0;
    $id = $_POST['id'];
    $stmt = $conn->prepare("UPDATE tools SET name=?, rate=?, is_retailer=? WHERE id=?");
    $stmt->bind_param('sdii', $name, $rate, $is_retailer, $id);
    $stmt->execute();
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head><title>Edit Tool</title></head>
<body>
<h1>Edit Tool</h1>
<form method="post">
    <input type="hidden" name="id" value="<?php echo $tool['id']; ?>">
    <label>Tool Name: <input type="text" name="tool_name" value="<?php echo htmlspecialchars($tool['name']); ?>" required></label><br>
    <label>Rate: <input type="number" name="rate" step="0.01" value="<?php echo $tool['rate']; ?>" required></label><br>
    <label>Retailer: <input type="checkbox" name="is_retailer" value="1" <?php if($tool['is_retailer']) echo 'checked'; ?>></label><br>
    <button type="submit">Update Tool</button>
</form>
</body>
</html>