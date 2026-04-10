<?php
require_once 'conn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../public/index.php');
    exit();
}

$invoice_type = $_POST['invoice_type']; // invoice, purchase, proforma, quotation
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

// Format the date to DD-MM-YYYY for storage
$date_obj = DateTime::createFromFormat('Y-m-d', $date_raw);
$date = $date_obj ? $date_obj->format('d-m-Y') : $date_raw;

// Collect items
$items = [];
if (isset($_POST['items'])) {
    foreach ($_POST['items'] as $item) {
        // Resolve tool name from ID
        $tool_name = $item['tool_id'];
        $tid = intval($item['tool_id']);
        if ($tid > 0) {
            $stmt = $conn->prepare("SELECT toolname FROM tools WHERE id = ?");
            $stmt->bind_param('i', $tid);
            $stmt->execute();
            $tres = $stmt->get_result();
            if ($trow = $tres->fetch_assoc()) $tool_name = $trow['toolname'];
            $stmt->close();
        }
        $items[] = [
            'tool_name' => $tool_name,
            'qty' => intval($item['qty']),
            'rate' => floatval($item['rate']),
            'discount_pct' => floatval($item['discount_pct'] ?? 0),
        ];
    }
}

// Get company address info
$address = '';
$state = '';
$district = '';
if ($company_id > 0) {
    $stmt = $conn->prepare("SELECT address, state, district FROM companydata WHERE id = ?");
    $stmt->bind_param('i', $company_id);
    $stmt->execute();
    $cres = $stmt->get_result();
    if ($crow = $cres->fetch_assoc()) {
        $address = $crow['address'] ?? '';
        $state = $crow['state'] ?? '';
        $district = $crow['district'] ?? '';
    }
    $stmt->close();
}

$db_error = '';
$saved = false;

if ($invoice_type === 'invoice') {
    // Save to delvin table (sales)
    if ($gst_type === 'tngst') {
        $stmt = $conn->prepare("INSERT INTO delvin (GSTNO, cname, bill, taxamt, cgst, sgst, Total, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssidddis', $gst_no, $company_name, $bill, $taxable_amount, $cgst, $sgst, $total, $date);
    } else {
        $stmt = $conn->prepare("INSERT INTO delvin (GSTNO, cname, bill, taxamt, igst, Total, date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssiddis', $gst_no, $company_name, $bill, $taxable_amount, $igst, $total, $date);
    }
    if (!$stmt->execute()) { $db_error = $conn->error; } else { $saved = true; }

} elseif ($invoice_type === 'purchase') {
    // Save to purchase table
    if ($gst_type === 'tngst') {
        $stmt = $conn->prepare("INSERT INTO purchase (GSTNO, cname, bill, taxamt, cgst, sgst, Total, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssidddis', $gst_no, $company_name, $bill, $taxable_amount, $cgst, $sgst, $total, $date);
    } else {
        $stmt = $conn->prepare("INSERT INTO purchase (GSTNO, cname, bill, taxamt, igst, Total, date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssiddis', $gst_no, $company_name, $bill, $taxable_amount, $igst, $total, $date);
    }
    if (!$stmt->execute()) { $db_error = $conn->error; } else { $saved = true; }
}
// proforma and quotation don't save to DB

if ($db_error) {
    echo "<div style='padding:40px;font-family:sans-serif;'><h3 style='color:red;'>Error saving record</h3><p>" . htmlspecialchars($db_error) . "</p><a href='javascript:history.back()'>Go Back</a></div>";
    exit();
}

// Determine title
$titles = [
    'invoice' => 'TAX INVOICE',
    'purchase' => 'PURCHASE INVOICE',
    'proforma' => 'PROFORMA INVOICE',
    'quotation' => 'QUOTATION',
];
$page_title = $titles[$invoice_type] ?? 'INVOICE';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - #<?php echo $bill; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        @page { size: A4; margin: 10mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #e9ecef; color: #1e293b; }
        .invoice-page {
            max-width: 800px; margin: 20px auto; background: #fff;
            border: 2px solid #1a2942; position: relative;
        }

        /* Header */
        .inv-header {
            background: #1a2942; color: #fff; padding: 20px 30px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .inv-header .brand h2 { font-size: 20px; font-weight: 800; letter-spacing: 1px; margin-bottom: 2px; }
        .inv-header .brand p { font-size: 11px; opacity: 0.8; }
        .inv-header .inv-type {
            background: #e8a838; color: #1a2942; font-weight: 800;
            padding: 8px 20px; border-radius: 4px; font-size: 14px; letter-spacing: 1px;
        }

        /* Info Section */
        .inv-info { padding: 20px 30px; display: flex; justify-content: space-between; border-bottom: 1px solid #e2e8f0; }
        .inv-info .block { }
        .inv-info .block h6 { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-bottom: 6px; }
        .inv-info .block p { font-size: 13px; margin-bottom: 2px; }
        .inv-info .block p strong { color: #1a2942; }

        /* GST Bar */
        .gst-bar {
            background: #f8fafc; padding: 10px 30px; border-bottom: 1px solid #e2e8f0;
            display: flex; justify-content: space-between; font-size: 12px;
        }
        .gst-bar span { color: #64748b; }
        .gst-bar strong { color: #1a2942; }

        /* Items Table */
        .inv-table { padding: 0 30px 20px; margin-top: 10px; }
        .inv-table table { width: 100%; border-collapse: collapse; }
        .inv-table thead th {
            background: #1a2942; color: #fff; padding: 10px 12px;
            font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; text-align: left;
        }
        .inv-table thead th:last-child, .inv-table tbody td:last-child { text-align: right; }
        .inv-table thead th:nth-child(3),
        .inv-table thead th:nth-child(4),
        .inv-table thead th:nth-child(5),
        .inv-table tbody td:nth-child(3),
        .inv-table tbody td:nth-child(4),
        .inv-table tbody td:nth-child(5) { text-align: center; }
        .inv-table tbody td { padding: 10px 12px; font-size: 13px; border-bottom: 1px solid #f1f5f9; }
        .inv-table tbody tr:nth-child(even) { background: #f8fafc; }

        /* Summary */
        .inv-summary {
            padding: 0 30px 20px; display: flex; justify-content: flex-end;
        }
        .summary-box {
            width: 300px; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden;
        }
        .summary-row {
            display: flex; justify-content: space-between; padding: 8px 16px;
            font-size: 13px; border-bottom: 1px solid #f1f5f9;
        }
        .summary-row.total {
            background: #1a2942; color: #fff; font-weight: 800; font-size: 16px;
            border: none; padding: 12px 16px;
        }

        /* Footer */
        .inv-footer {
            padding: 15px 30px; border-top: 2px solid #1a2942;
            display: flex; justify-content: space-between; align-items: flex-end;
            font-size: 11px; color: #64748b;
        }
        .inv-footer .sign { text-align: right; }
        .inv-footer .sign .line { border-top: 1px solid #1a2942; padding-top: 4px; margin-top: 30px; width: 200px; display: inline-block; }

        /* Note bar */
        .note-bar {
            background: #fef3cd; padding: 8px 30px; font-size: 12px; color: #856404;
            border-bottom: 1px solid #e2e8f0;
        }

        /* Actions */
        .actions-bar {
            text-align: center; padding: 20px; background: #e9ecef;
        }
        .actions-bar .btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 10px 24px; border: none; border-radius: 6px;
            font-size: 14px; font-weight: 600; cursor: pointer;
            text-decoration: none; margin: 0 6px; transition: all 0.2s;
        }
        .btn-print { background: #1a2942; color: #fff; }
        .btn-print:hover { background: #2c3e5a; color: #fff; }
        .btn-download { background: #3b82f6; color: #fff; }
        .btn-download:hover { background: #2563eb; color: #fff; }
        .btn-dash { background: #fff; color: #1a2942; border: 1px solid #d1d5db; }
        .btn-dash:hover { background: #f1f5f9; color: #1a2942; }
        .btn-new { background: #22c55e; color: #fff; }
        .btn-new:hover { background: #16a34a; color: #fff; }

        @media print {
            body { background: #fff; }
            .actions-bar { display: none !important; }
            .invoice-page { border: none; margin: 0; box-shadow: none; }
        }
    </style>
</head>
<body>

<div class="invoice-page">
    <!-- Header -->
    <div class="inv-header">
        <div class="brand">
            <h2>DELVIN DIAMOND TOOL INDUSTRIES</h2>
            <p>1/56, Easu Street, Somarasampettai (PO), Trichy - 620 102</p>
        </div>
        <div class="inv-type"><?php echo $page_title; ?></div>
    </div>

    <!-- GST Bar -->
    <div class="gst-bar">
        <div><span>GSTIN:</span> <strong>33AAAPY1027F1Z3</strong></div>
        <div><span>HSN Code:</span> <strong>68042110</strong></div>
        <div><span>Invoice #:</span> <strong><?php echo $bill; ?></strong></div>
        <div><span>Date:</span> <strong><?php echo htmlspecialchars($date); ?></strong></div>
    </div>



    <!-- Bill To / Ship To -->
    <div class="inv-info">
        <div class="block">
            <h6>Bill To</h6>
            <p><strong><?php echo htmlspecialchars($company_name); ?></strong></p>
            <p><?php echo htmlspecialchars($address); ?></p>
            <p><?php echo htmlspecialchars($district); ?><?php echo $district && $state ? ', ' : ''; ?><?php echo htmlspecialchars($state); ?></p>
        </div>
        <div class="block" style="text-align:right;">
            <h6>Customer GST</h6>
            <p><strong><?php echo htmlspecialchars($gst_no); ?></strong></p>
            <p>Type: <?php echo strtoupper($gst_type); ?></p>
        </div>
    </div>

    <!-- Items Table -->
    <?php if (count($items) > 0): ?>
    <div class="inv-table">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Rate (₹)</th>
                    <th>Discount</th>
                    <th>Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $sno = 1;
            foreach ($items as $it):
                $amt = $it['qty'] * $it['rate'];
                if ($it['discount_pct'] > 0) $amt -= $amt * $it['discount_pct'] / 100;
            ?>
                <tr>
                    <td><?php echo $sno++; ?></td>
                    <td><?php echo htmlspecialchars($it['tool_name']); ?></td>
                    <td><?php echo $it['qty']; ?></td>
                    <td><?php echo number_format($it['rate'], 2); ?></td>
                    <td><?php echo $it['discount_pct'] > 0 ? $it['discount_pct'] . '%' : '-'; ?></td>
                    <td><?php echo number_format($amt, 2); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- Summary -->
    <div class="inv-summary">
        <div class="summary-box">
            <div class="summary-row">
                <span>Taxable Amount</span>
                <span>₹ <?php echo number_format($taxable_amount, 2); ?></span>
            </div>
            <?php if ($gst_type === 'tngst'): ?>
            <div class="summary-row">
                <span>CGST (9%)</span>
                <span>₹ <?php echo number_format($cgst, 2); ?></span>
            </div>
            <div class="summary-row">
                <span>SGST (9%)</span>
                <span>₹ <?php echo number_format($sgst, 2); ?></span>
            </div>
            <?php else: ?>
            <div class="summary-row">
                <span>IGST (18%)</span>
                <span>₹ <?php echo number_format($igst, 2); ?></span>
            </div>
            <?php endif; ?>
            <div class="summary-row total">
                <span>TOTAL</span>
                <span>₹ <?php echo number_format($total, 2); ?></span>
            </div>
        </div>
    </div>

    <!-- Amount in Words & Bank Details -->
    <div style="padding: 10px 30px; border-top: 1px solid #e2e8f0; font-size: 12px;">
        <p style="margin-bottom: 8px;"><strong>Amount in words:</strong> RUPEES <?php
            $ones = ['', 'ONE', 'TWO', 'THREE', 'FOUR', 'FIVE', 'SIX', 'SEVEN', 'EIGHT', 'NINE', 'TEN',
                'ELEVEN', 'TWELVE', 'THIRTEEN', 'FOURTEEN', 'FIFTEEN', 'SIXTEEN', 'SEVENTEEN', 'EIGHTEEN', 'NINETEEN'];
            $tens = ['', '', 'TWENTY', 'THIRTY', 'FORTY', 'FIFTY', 'SIXTY', 'SEVENTY', 'EIGHTY', 'NINETY'];
            function numToWords($n) {
                global $ones, $tens;
                if ($n < 0) return 'MINUS ' . numToWords(-$n);
                if ($n == 0) return 'ZERO';
                $w = '';
                if (intval($n / 10000000) > 0) { $w .= numToWords(intval($n/10000000)) . ' CRORE '; $n %= 10000000; }
                if (intval($n / 100000) > 0) { $w .= numToWords(intval($n/100000)) . ' LAKH '; $n %= 100000; }
                if (intval($n / 1000) > 0) { $w .= numToWords(intval($n/1000)) . ' THOUSAND '; $n %= 1000; }
                if (intval($n / 100) > 0) { $w .= $ones[intval($n/100)] . ' HUNDRED '; $n %= 100; }
                if ($n > 0) { if ($w != '') $w .= 'AND '; if ($n < 20) { $w .= $ones[$n]; } else { $w .= $tens[intval($n/10)]; if ($n%10) $w .= ' ' . $ones[$n%10]; } }
                return trim($w);
            }
            echo numToWords($total) . ' ONLY.';
        ?></p>
        <p style="color: #64748b;">BANK: UCO BANK, SOMARASAMPETTAI | A/C: 07640500000016 | IFSC: UCBA0000764</p>
    </div>

    <!-- Footer -->
    <div class="inv-footer">
        <div>
            <p>Thank you for your business.</p>
            <p>E. & O. E.</p>
        </div>
        <div class="sign">
            <div class="line">Authorised Signatory</div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="actions-bar">
    <button class="btn btn-print" onclick="window.print()"><i class="bi bi-printer"></i> Print Invoice</button>
    <button class="btn btn-download" onclick="downloadPDF()"><i class="bi bi-download"></i> Download PDF</button>
    <a href="../public/index.php" class="btn btn-dash"><i class="bi bi-grid-1x2"></i> Dashboard</a>
    <?php if ($invoice_type === 'invoice'): ?>
    <a href="../public/create_invoice.php" class="btn btn-new"><i class="bi bi-plus-circle"></i> New Invoice</a>
    <?php elseif ($invoice_type === 'purchase'): ?>
    <a href="../public/create_purchase.php" class="btn btn-new"><i class="bi bi-plus-circle"></i> New Purchase</a>
    <?php elseif ($invoice_type === 'proforma'): ?>
    <a href="../public/proforma_invoice.php" class="btn btn-new"><i class="bi bi-plus-circle"></i> New Proforma</a>
    <?php elseif ($invoice_type === 'quotation'): ?>
    <a href="../public/quotation.php" class="btn btn-new"><i class="bi bi-plus-circle"></i> New Quotation</a>
    <?php endif; ?>
</div>

<script>
function downloadPDF() {
    var element = document.querySelector('.invoice-page');
    var opt = {
        margin: 10,
        filename: '<?php echo $page_title; ?>_<?php echo $bill; ?>_<?php echo htmlspecialchars($company_name); ?>.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };
    html2pdf().set(opt).from(element).save();
}
</script>
</body>
</html>