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
</head>
<body>
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