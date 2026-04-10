<?php
// Add Tool page
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Tool</title>
</head>
<body>
    <h1>Add Tool</h1>
    <form method="post" action="../src/add_tool.php">
        <label>Tool Name: <input type="text" name="tool_name" required></label><br>
        <label>Rate: <input type="number" name="rate" step="0.01" required></label><br>
        <label>Retailer: <input type="checkbox" name="is_retailer" value="1"></label><br>
        <button type="submit">Add Tool</button>
    </form>
</body>
</html>