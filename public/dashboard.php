<?php
require_once '../src/conn.php';
// Get company count
$company_count = 0;
$order_count = 0;
$month = date('m');
$year = date('Y');

// Company count
$result = $conn->query("SELECT COUNT(*) as cnt FROM companies");
if ($result) {
    $row = $result->fetch_assoc();
    $company_count = $row['cnt'];
}
// Orders this month
$result = $conn->query("SELECT COUNT(*) as cnt FROM invoices WHERE MONTH(date) = $month AND YEAR(date) = $year");
if ($result) {
    $row = $result->fetch_assoc();
    $order_count = $row['cnt'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>
    <h1>Dashboard</h1>
    <div id="stats">
        <p>Companies: <span id="company-count"><?php echo $company_count; ?></span></p>
        <p>Orders this month: <span id="order-count"><?php echo $order_count; ?></span></p>
    </div>
</body>
</html>