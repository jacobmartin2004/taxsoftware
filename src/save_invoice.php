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
$delivery_challan = isset($_POST['delivery_challan']) ? trim($_POST['delivery_challan']) : '';
$despatch = isset($_POST['despatch']) ? trim($_POST['despatch']) : 'COURIER';

// Format the date to DD-MM-YYYY for storage
$date_obj = DateTime::createFromFormat('Y-m-d', $date_raw);
$date = $date_obj ? $date_obj->format('d-m-Y') : $date_raw;
// Short date format for display
$date_short = $date_obj ? $date_obj->format('d.n.y') : $date_raw;

// Collect items
$items = [];
if (isset($_POST['items'])) {
    foreach ($_POST['items'] as $item) {
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
$state_code = '';
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
// Extract state code from GST number (first 2 digits)
if (strlen($gst_no) >= 2) {
    $state_code = substr($gst_no, 0, 2);
}

$db_error = '';
$saved = false;

if ($invoice_type === 'invoice') {
    if ($gst_type === 'tngst' || $gst_type === '25p' || $gst_type === '6p') {
        $stmt = $conn->prepare("INSERT INTO delvin (GSTNO, cname, bill, taxamt, cgst, sgst, Total, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssidddis', $gst_no, $company_name, $bill, $taxable_amount, $cgst, $sgst, $total, $date);
    } else {
        $stmt = $conn->prepare("INSERT INTO delvin (GSTNO, cname, bill, taxamt, igst, Total, date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssiddis', $gst_no, $company_name, $bill, $taxable_amount, $igst, $total, $date);
    }
    if (!$stmt->execute()) { $db_error = $conn->error; } else { $saved = true; }
} elseif ($invoice_type === 'purchase') {
    if ($gst_type === 'tngst' || $gst_type === '25p' || $gst_type === '6p') {
        $stmt = $conn->prepare("INSERT INTO purchase (GSTNO, cname, bill, taxamt, cgst, sgst, Total, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssidddis', $gst_no, $company_name, $bill, $taxable_amount, $cgst, $sgst, $total, $date);
    } else {
        $stmt = $conn->prepare("INSERT INTO purchase (GSTNO, cname, bill, taxamt, igst, Total, date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssiddis', $gst_no, $company_name, $bill, $taxable_amount, $igst, $total, $date);
    }
    if (!$stmt->execute()) { $db_error = $conn->error; } else { $saved = true; }
}

if ($db_error) {
    echo "<div style='padding:40px;font-family:sans-serif;'><h3 style='color:red;'>Error saving record</h3><p>" . htmlspecialchars($db_error) . "</p><a href='javascript:history.back()'>Go Back</a></div>";
    exit();
}

$titles = [
    'invoice' => 'INVOICE',
    'purchase' => 'PURCHASE INVOICE',
    'proforma' => 'PROFORMA INVOICE',
    'quotation' => 'QUOTATION',
];
$page_title = $titles[$invoice_type] ?? 'INVOICE';

// Number to words function
function numToWords($n) {
    $ones = ['', 'ONE', 'TWO', 'THREE', 'FOUR', 'FIVE', 'SIX', 'SEVEN', 'EIGHT', 'NINE', 'TEN',
        'ELEVEN', 'TWELVE', 'THIRTEEN', 'FOURTEEN', 'FIFTEEN', 'SIXTEEN', 'SEVENTEEN', 'EIGHTEEN', 'NINETEEN'];
    $tens_arr = ['', '', 'TWENTY', 'THIRTY', 'FORTY', 'FIFTY', 'SIXTY', 'SEVENTY', 'EIGHTY', 'NINETY'];
    if ($n < 0) return 'MINUS ' . numToWords(-$n);
    if ($n == 0) return 'ZERO';
    $w = '';
    if (intval($n / 10000000) > 0) { $w .= numToWords(intval($n/10000000)) . ' CRORE '; $n %= 10000000; }
    if (intval($n / 100000) > 0) { $w .= numToWords(intval($n/100000)) . ' LAKH '; $n %= 100000; }
    if (intval($n / 1000) > 0) { $w .= numToWords(intval($n/1000)) . ' THOUSAND '; $n %= 1000; }
    if (intval($n / 100) > 0) { $w .= $ones[intval($n/100)] . ' HUNDRED '; $n %= 100; }
    if ($n > 0) { if ($w != '') $w .= 'AND '; if ($n < 20) { $w .= $ones[$n]; } else { $w .= $tens_arr[intval($n/10)]; if ($n%10) $w .= ' ' . $ones[$n%10]; } }
    return trim($w);
}
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
        .inv-header .brand h2 { font-size: 22px; font-weight: 900; letter-spacing: 1px; margin-bottom: 2px; color: #fff; }
        .inv-header .brand p { font-size: 11px; opacity: 0.8; }
        .inv-header .inv-type {
            background: #e8a838; color: #1a2942; font-weight: 800;
            padding: 8px 20px; border-radius: 4px; font-size: 14px; letter-spacing: 1px;
        }

        /* GST Bar */
        .gst-bar {
            background: #f8fafc; padding: 10px 30px; border-bottom: 1px solid #e2e8f0;
            display: flex; justify-content: space-between; font-size: 12px;
        }
        .gst-bar span { color: #64748b; }
        .gst-bar strong { color: #1a2942; }

        /* Info Section */
        .inv-info { padding: 20px 30px; display: flex; justify-content: space-between; border-bottom: 1px solid #e2e8f0; }
        .inv-info .block h6 { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-bottom: 6px; }
        .inv-info .block p { font-size: 13px; margin-bottom: 2px; }
        .inv-info .block p strong { color: #1a2942; }

        /* Items Table */
        .inv-table { padding: 0 30px 20px; margin-top: 10px; }
        .inv-table table { width: 100%; border-collapse: collapse; }
        .inv-table thead th {
            background: #1a2942; color: #fff; padding: 10px 12px;
            font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; text-align: left;
        }
        .inv-table thead th:last-child { text-align: right; }
        .inv-table thead th.text-center { text-align: center; }
        .inv-table tbody td { padding: 10px 12px; font-size: 13px; border-bottom: 1px solid #f1f5f9; }
        .inv-table tbody td:last-child { text-align: right; }
        .inv-table tbody td.text-center { text-align: center; }
        .inv-table tbody tr:nth-child(even) { background: #f8fafc; }
        .inv-table .discount-sub { color: #888; font-size: 12px; font-style: italic; }

        /* Summary */
        .inv-summary { padding: 0 30px 20px; display: flex; justify-content: flex-end; }
        .summary-box { width: 300px; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; }
        .summary-row { display: flex; justify-content: space-between; padding: 8px 16px; font-size: 13px; border-bottom: 1px solid #f1f5f9; }
        .summary-row.total { background: #1a2942; color: #fff; font-weight: 800; font-size: 16px; border: none; padding: 12px 16px; }

        /* Footer Section */
        .inv-words-bank { padding: 10px 30px; border-top: 1px solid #e2e8f0; font-size: 12px; }
        .inv-words-bank .words { font-weight: 700; margin-bottom: 8px; }
        .inv-words-bank .bank { color: #64748b; }
        .inv-footer {
            padding: 15px 30px; border-top: 2px solid #1a2942;
            display: flex; justify-content: space-between; align-items: flex-end;
            font-size: 11px; color: #64748b;
        }
        .inv-footer .sign { text-align: right; }
        .inv-footer .sign .line { border-top: 1px solid #1a2942; padding-top: 4px; margin-top: 30px; width: 200px; display: inline-block; }

        /* Actions */
        .actions-bar { text-align: center; padding: 20px; background: #e9ecef; }
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

<div class="invoice-page" id="invoicePage">
    <!-- Header -->
    <div class="inv-header">
        <div class="brand">
            <h2>DELVIN DIAMOND TOOL INDUSTRIES</h2>
            <p>1/56, Easu Street, Somarasampettai (PO), Trichy - 620 102</p>
            <p>Ph: 0431-2607224, 098424 07224 | Email: delvinvincent@yahoo.com</p>
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
            <p><strong>M/S. <?php echo htmlspecialchars($company_name); ?></strong></p>
            <?php if ($address): ?><p><?php echo htmlspecialchars($address); ?></p><?php endif; ?>
            <p><?php echo htmlspecialchars($district); ?><?php echo $district && $state ? ', ' : ''; ?><?php echo htmlspecialchars($state); ?></p>
        </div>
        <div class="block" style="text-align:right;">
            <h6>Customer GST</h6>
            <p><strong><?php echo htmlspecialchars($gst_no); ?></strong></p>
            <p>Type: <?php echo strtoupper($gst_type); ?></p>
            <?php if ($state_code): ?><p>State Code: <?php echo htmlspecialchars($state_code); ?></p><?php endif; ?>
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
                    <th class="text-center">Qty</th>
                    <th class="text-center">Rate (&#8377;)</th>
                    <th>Amount (&#8377;)</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $sno = 1;
            foreach ($items as $it):
                $gross = $it['qty'] * $it['rate'];
                $disc_amt = 0;
                $net = $gross;
                if ($it['discount_pct'] > 0) {
                    $disc_amt = $gross * $it['discount_pct'] / 100;
                    $net = $gross - $disc_amt;
                }
            ?>
                <tr>
                    <td><?php echo $sno++; ?></td>
                    <td>
                        <?php echo htmlspecialchars(strtoupper($it['tool_name'])); ?>
                        <?php if ($it['discount_pct'] > 0): ?>
                        <br><span class="discount-sub">&nbsp;&nbsp;Less: Discount <?php echo $it['discount_pct']; ?>% = &#8377;<?php echo number_format($disc_amt, 2); ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><?php echo $it['qty']; ?></td>
                    <td class="text-center"><?php echo number_format($it['rate'], 2); ?></td>
                    <td><?php echo number_format($net, 2); ?></td>
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
                <span>&#8377; <?php echo number_format($taxable_amount, 2); ?></span>
            </div>
            <?php if ($gst_type === 'tngst'): ?>
            <div class="summary-row">
                <span>CGST (9%)</span>
                <span>&#8377; <?php echo number_format($cgst, 2); ?></span>
            </div>
            <div class="summary-row">
                <span>SGST (9%)</span>
                <span>&#8377; <?php echo number_format($sgst, 2); ?></span>
            </div>
            <?php elseif ($gst_type === '25p'): ?>
            <div class="summary-row">
                <span>CGST (12.5%)</span>
                <span>&#8377; <?php echo number_format($cgst, 2); ?></span>
            </div>
            <div class="summary-row">
                <span>SGST (12.5%)</span>
                <span>&#8377; <?php echo number_format($sgst, 2); ?></span>
            </div>
            <?php elseif ($gst_type === '6p'): ?>
            <div class="summary-row">
                <span>CGST (3%)</span>
                <span>&#8377; <?php echo number_format($cgst, 2); ?></span>
            </div>
            <div class="summary-row">
                <span>SGST (3%)</span>
                <span>&#8377; <?php echo number_format($sgst, 2); ?></span>
            </div>
            <?php else: ?>
            <div class="summary-row">
                <span>IGST (18%)</span>
                <span>&#8377; <?php echo number_format($igst, 2); ?></span>
            </div>
            <?php endif; ?>
            <div class="summary-row total">
                <span>TOTAL</span>
                <span>&#8377; <?php echo number_format($total, 2); ?></span>
            </div>
        </div>
    </div>

    <!-- Amount in Words & Bank Details -->
    <div class="inv-words-bank">
        <p class="words">Amount in words: RUPEES <?php echo numToWords($total); ?> ONLY.</p>
        <p class="bank">BANK: UCO BANK, SOMARASAMPETTAI | A/C: 07640500000016 | IFSC: UCBA0000764</p>
    </div>

    <!-- Footer -->
    <div class="inv-footer">
        <div>
            <p>E. & O. E.</p>
        </div>
        <div class="sign">
            <p style="font-weight:700; color:#1a2942;">for DELVIN DIAMOND TOOL INDUSTRIES</p>
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
    var element = document.getElementById('invoicePage');
    var opt = {
        margin: 10,
        filename: '<?php echo $page_title; ?>_<?php echo $bill; ?>_<?php echo preg_replace('/[^A-Za-z0-9]/', '_', $company_name); ?>.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };
    html2pdf().set(opt).from(element).save();
}
</script>
</body>
</html>