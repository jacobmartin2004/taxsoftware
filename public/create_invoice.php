<?php
require_once '../src/conn.php';
// Helper: get companies and tools for dropdowns
function getCompanies($conn) {
    $res = $conn->query("SELECT id, name FROM companies");
    $companies = [];
    while ($row = $res->fetch_assoc()) $companies[] = $row;
    return $companies;
}
function getTools($conn) {
    $res = $conn->query("SELECT id, name, rate, is_retailer FROM tools");
    $tools = [];
    while ($row = $res->fetch_assoc()) $tools[] = $row;
    return $tools;
}
$companies = getCompanies($conn);
$tools = getTools($conn);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Invoice</title>
    <script>
    function updateTax() {
        var state = document.getElementById('state').value.toLowerCase();
        var taxLabel = document.getElementById('tax-label');
        if(state === 'tamil nadu') {
            taxLabel.innerText = 'CGST 9% + SGST 9%';
        } else {
            taxLabel.innerText = 'IGST 18%';
        }
    }
    </script>
</head>
<body>
    <h1>Create Invoice</h1>
    <form method="post" action="../src/create_invoice.php">
        <label>Company:
            <select name="company_id" required onchange="document.getElementById('state').value=this.options[this.selectedIndex].getAttribute('data-state'); updateTax();">
                <option value="">Select Company</option>
                <?php foreach($companies as $c): ?>
                <option value="<?php echo $c['id']; ?>" data-state="<?php echo htmlspecialchars($c['name']); ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </label><br>
        <input type="hidden" id="state" name="state" value="">
        <label>Tools:</label><br>
        <?php foreach($tools as $t): ?>
            <input type="checkbox" name="tool_ids[]" value="<?php echo $t['id']; ?>"> <?php echo htmlspecialchars($t['name']); ?> (Rate: <?php echo $t['rate']; ?>, Retailer: <?php echo $t['is_retailer'] ? 'Yes' : 'No'; ?>)<br>
        <?php endforeach; ?>
        <label>Quantity (for each tool, comma separated): <input type="text" name="quantities" placeholder="e.g. 2,1,5" required></label><br>
        <span id="tax-label">Tax: </span><br>
        <button type="submit">Create Invoice</button>
    </form>
</body>
</html>