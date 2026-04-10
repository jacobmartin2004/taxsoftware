<?php
// Add Company page
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Company</title>
</head>
<body>
    <h1>Add Company</h1>
    <form method="post" action="../src/add_company.php">
        <label>Company Name: <input type="text" name="company_name" required></label><br>
        <label>Address: <input type="text" name="address" required></label><br>
        <label>State: <input type="text" name="state" required></label><br>
        <label>District: <input type="text" name="district" required></label><br>
        <label>GST No: <input type="text" name="gst_no" required></label><br>
        <button type="submit">Add Company</button>
    </form>
</body>
</html>