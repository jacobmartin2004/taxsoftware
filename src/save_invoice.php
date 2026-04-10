<?php
require_once 'conn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../public/dashboard.php');
    exit();
}

$invoice_type = $_POST['invoice_type']; // invoice, proforma, quotation
$company_id = intval($_POST['company_id']);
$bill = intval($_POST['bill']);
$date_raw = $_POST['date'];
$gst_no = strtoupper(trim($_POST['gst_no']));
$gst_type = $_POST['gst_type'];
$company_name = strtoupper(trim($_POST['company_name']));
$taxable_amount = floatval($_POST['taxable_amount']);
$cgst = floatval($_POST['cgst']);
$sgst = floatval($_POST['sgst']);
$igst = floatval($_POST['igst']);
$total = intval($_POST['total']);

// Format the date to DD-MM-YYYY for storage (matching existing format)
$date_obj = DateTime::createFromFormat('Y-m-d', $date_raw);
$date = $date_obj ? $date_obj->format('d-m-Y') : $date_raw;

if ($invoice_type === 'invoice') {
    // Save to delvin table (sales) - this goes to tax report
    if ($gst_type === 'tngst') {
        $stmt = $conn->prepare("INSERT INTO delvin (GSTNO, cname, bill, taxamt, cgst, sgst, Total, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssidddis', $gst_no, $company_name, $bill, $taxable_amount, $cgst, $sgst, $total, $date);
    } else {
        $stmt = $conn->prepare("INSERT INTO delvin (GSTNO, cname, bill, taxamt, igst, Total, date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssiddis', $gst_no, $company_name, $bill, $taxable_amount, $igst, $total, $date);
    }
    $stmt->execute();

    if ($conn->error) {
        echo "Error: " . $conn->error;
        exit();
    }
    header('Location: ../src/view.php');
    exit();

} elseif ($invoice_type === 'purchase') {
    // Save to purchase table - this goes to tax report
    if ($gst_type === 'tngst') {
        $stmt = $conn->prepare("INSERT INTO purchase (GSTNO, cname, bill, taxamt, cgst, sgst, Total, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssidddis', $gst_no, $company_name, $bill, $taxable_amount, $cgst, $sgst, $total, $date);
    } else {
        $stmt = $conn->prepare("INSERT INTO purchase (GSTNO, cname, bill, taxamt, igst, Total, date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssiddis', $gst_no, $company_name, $bill, $taxable_amount, $igst, $total, $date);
    }
    $stmt->execute();

    if ($conn->error) {
        echo "Error: " . $conn->error;
        exit();
    }
    header('Location: ../public/view1.php');
    exit();

} elseif ($invoice_type === 'proforma') {
    // Proforma - does NOT add to tax report, just show confirmation
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Proforma Invoice Created</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>@media print { .no-print { display: none; } }</style>
    </head>
    <body>
    <div class="container mt-4" style="max-width:800px;">
        <h2 class="text-center">DELVIN DIAMOND TOOL INDUSTRIES</h2>
        <p class="text-center">1/56, Easu Street, Somarasampettai (PO), Trichy - 620 102</p>
        <hr>
        <h4 class="text-center">PROFORMA INVOICE</h4>
        <p><strong>To:</strong> <?php echo htmlspecialchars($company_name); ?></p>
        <p><strong>GST No:</strong> <?php echo htmlspecialchars($gst_no); ?></p>
        <p><strong>Invoice No:</strong> <?php echo $bill; ?> | <strong>Date:</strong> <?php echo htmlspecialchars($date); ?></p>
        <hr>
        <table class="table table-bordered">
            <thead><tr><th>S.No</th><th>Tool</th><th>Qty</th><th>Rate</th><th>Discount%</th><th>Amount</th></tr></thead>
            <tbody>
            <?php
            $sno = 1;
            if (isset($_POST['items'])) {
                foreach ($_POST['items'] as $item) {
                    $qty = intval($item['qty']);
                    $rate = floatval($item['rate']);
                    $disc = floatval($item['discount_pct'] ?? 0);
                    $amt = $qty * $rate;
                    if ($disc > 0) $amt -= $amt * $disc / 100;
                    echo "<tr><td>$sno</td><td>" . htmlspecialchars($item['tool_id']) . "</td><td>$qty</td><td>" . number_format($rate,2) . "</td><td>$disc</td><td>" . number_format($amt,2) . "</td></tr>";
                    $sno++;
                }
            }
            ?>
            </tbody>
        </table>
        <p><strong>Taxable Amount:</strong> ₹<?php echo number_format($taxable_amount, 2); ?></p>
        <?php if ($gst_type === 'tngst'): ?>
            <p>CGST (9%): ₹<?php echo number_format($cgst, 2); ?></p>
            <p>SGST (9%): ₹<?php echo number_format($sgst, 2); ?></p>
        <?php else: ?>
            <p>IGST (18%): ₹<?php echo number_format($igst, 2); ?></p>
        <?php endif; ?>
        <h4>TOTAL: ₹<?php echo number_format($total, 2); ?></h4>
        <p><em>Note: This is a Proforma Invoice and is NOT added to the tax report.</em></p>
        <div class="no-print mt-3">
            <button onclick="window.print()" class="btn btn-primary">Print</button>
            <a href="../public/dashboard.php" class="btn btn-secondary">Dashboard</a>
        </div>
    </div>
    </body>
    </html>
    <?php
    exit();

} elseif ($invoice_type === 'quotation') {
    // Quotation - does NOT add to tax report
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Quotation Created</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>@media print { .no-print { display: none; } }</style>
    </head>
    <body>
    <div class="container mt-4" style="max-width:800px;">
        <h2 class="text-center">DELVIN DIAMOND TOOL INDUSTRIES</h2>
        <p class="text-center">1/56, Easu Street, Somarasampettai (PO), Trichy - 620 102</p>
        <hr>
        <h4 class="text-center">QUOTATION</h4>
        <p><strong>To:</strong> <?php echo htmlspecialchars($company_name); ?></p>
        <p><strong>GST No:</strong> <?php echo htmlspecialchars($gst_no); ?></p>
        <p><strong>Ref No:</strong> <?php echo $bill; ?> | <strong>Date:</strong> <?php echo htmlspecialchars($date); ?></p>
        <hr>
        <table class="table table-bordered">
            <thead><tr><th>S.No</th><th>Tool</th><th>Qty</th><th>Rate</th><th>Discount%</th><th>Amount</th></tr></thead>
            <tbody>
            <?php
            $sno = 1;
            if (isset($_POST['items'])) {
                foreach ($_POST['items'] as $item) {
                    $qty = intval($item['qty']);
                    $rate = floatval($item['rate']);
                    $disc = floatval($item['discount_pct'] ?? 0);
                    $amt = $qty * $rate;
                    if ($disc > 0) $amt -= $amt * $disc / 100;
                    echo "<tr><td>$sno</td><td>" . htmlspecialchars($item['tool_id']) . "</td><td>$qty</td><td>" . number_format($rate,2) . "</td><td>$disc</td><td>" . number_format($amt,2) . "</td></tr>";
                    $sno++;
                }
            }
            ?>
            </tbody>
        </table>
        <p><strong>Taxable Amount:</strong> ₹<?php echo number_format($taxable_amount, 2); ?></p>
        <?php if ($gst_type === 'tngst'): ?>
            <p>CGST (9%): ₹<?php echo number_format($cgst, 2); ?></p>
            <p>SGST (9%): ₹<?php echo number_format($sgst, 2); ?></p>
        <?php else: ?>
            <p>IGST (18%): ₹<?php echo number_format($igst, 2); ?></p>
        <?php endif; ?>
        <h4>TOTAL: ₹<?php echo number_format($total, 2); ?></h4>
        <p><em>This is a Quotation only, NOT added to the tax report.</em></p>
        <div class="no-print mt-3">
            <button onclick="window.print()" class="btn btn-primary">Print</button>
            <a href="../public/dashboard.php" class="btn btn-secondary">Dashboard</a>
        </div>
    </div>
    </body>
    </html>
    <?php
    exit();
}
?>