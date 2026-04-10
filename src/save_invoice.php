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
    if ($gst_type === 'tngst') {
        $stmt = $conn->prepare("INSERT INTO delvin (GSTNO, cname, bill, taxamt, cgst, sgst, Total, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssidddis', $gst_no, $company_name, $bill, $taxable_amount, $cgst, $sgst, $total, $date);
    } else {
        $stmt = $conn->prepare("INSERT INTO delvin (GSTNO, cname, bill, taxamt, igst, Total, date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssiddis', $gst_no, $company_name, $bill, $taxable_amount, $igst, $total, $date);
    }
    if (!$stmt->execute()) { $db_error = $conn->error; } else { $saved = true; }
} elseif ($invoice_type === 'purchase') {
    if ($gst_type === 'tngst') {
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
        @page { size: A4; margin: 8mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Times New Roman', Times, serif; background: #d0d0d0; color: #000; font-size: 13px; }

        .invoice-page {
            max-width: 780px; margin: 16px auto; background: #fff;
            border: 2px solid #000; position: relative;
            padding: 0;
        }

        /* === TOP HEADER === */
        .inv-top-header {
            display: flex; justify-content: space-between; align-items: flex-start;
            padding: 12px 16px 8px; border-bottom: 2px solid #000;
        }
        .inv-top-header .left { font-size: 11px; }
        .inv-top-header .center { text-align: center; flex: 1; }
        .inv-top-header .center .title-label {
            font-size: 13px; font-weight: bold; margin-bottom: 4px;
        }
        .inv-top-header .center .company-name {
            font-size: 18px; font-weight: 900; color: #1a2942; letter-spacing: 1px;
            margin-bottom: 2px;
        }
        .inv-top-header .center .addr { font-size: 11px; margin-bottom: 1px; }
        .inv-top-header .right { text-align: right; font-size: 11px; }
        .inv-top-header .right .hsn { font-weight: bold; }

        /* === TO + DETAILS SECTION === */
        .inv-details {
            display: flex; border-bottom: 1px solid #000;
        }
        .inv-details .to-section {
            width: 55%; padding: 10px 16px; border-right: 1px solid #000;
        }
        .inv-details .to-section .label { font-size: 11px; font-weight: bold; margin-bottom: 2px; }
        .inv-details .to-section p { font-size: 12px; margin-bottom: 1px; }
        .inv-details .to-section .cname { font-size: 13px; font-weight: bold; }

        .inv-details .meta-section {
            width: 45%; padding: 0;
        }
        .meta-row {
            display: flex; border-bottom: 1px solid #ccc; font-size: 11px;
        }
        .meta-row:last-child { border-bottom: none; }
        .meta-row .mk { padding: 5px 8px; width: 55%; font-weight: bold; border-right: 1px solid #ccc; }
        .meta-row .mv { padding: 5px 8px; width: 45%; }

        /* === GST ROW === */
        .gst-row {
            display: flex; border-bottom: 1px solid #000; font-size: 11px;
        }
        .gst-row div { padding: 4px 8px; border-right: 1px solid #ccc; }
        .gst-row div:last-child { border-right: none; }
        .gst-row .label { font-weight: bold; }

        /* === LETTER TEXT === */
        .letter-text {
            padding: 8px 16px; font-size: 11px; line-height: 1.5;
            border-bottom: 1px solid #000; font-style: italic;
        }

        /* === ITEMS TABLE === */
        .inv-items { width: 100%; border-collapse: collapse; }
        .inv-items th {
            background: #1a2942; color: #fff; padding: 7px 8px;
            font-size: 11px; text-transform: uppercase; font-weight: 700;
            border: 1px solid #000; text-align: center;
        }
        .inv-items td {
            padding: 6px 8px; font-size: 12px; border: 1px solid #ccc;
            vertical-align: top;
        }
        .inv-items .text-right { text-align: right; }
        .inv-items .text-center { text-align: center; }
        .inv-items .discount-row td { font-size: 11px; font-style: italic; color: #555; }
        .inv-items .taxable-row td { font-weight: bold; border-top: 2px solid #000; }
        .inv-items .tax-row td { font-size: 12px; }
        .inv-items .total-row td {
            font-weight: 900; font-size: 14px; border-top: 2px solid #000;
            background: #f5f5f5;
        }

        /* === BOTTOM SECTION === */
        .inv-bottom {
            display: flex; border-top: 2px solid #000;
        }
        .inv-bottom .left-bottom {
            width: 60%; padding: 10px 16px; border-right: 1px solid #000;
            font-size: 11px;
        }
        .inv-bottom .left-bottom .amount-words {
            font-weight: bold; text-transform: uppercase; margin-bottom: 8px;
        }
        .inv-bottom .right-bottom {
            width: 40%; padding: 10px 16px; text-align: center; font-size: 12px;
        }
        .inv-bottom .right-bottom .for-text { font-weight: bold; margin-bottom: 40px; }
        .inv-bottom .right-bottom .sign-line {
            border-top: 1px solid #000; display: inline-block; padding-top: 4px;
            width: 180px; font-size: 11px;
        }

        /* === BANK BAR === */
        .bank-bar {
            padding: 6px 16px; border-top: 1px solid #000;
            font-size: 10px; font-weight: bold; background: #f8f8f8;
        }

        /* === ACTIONS === */
        .actions-bar {
            text-align: center; padding: 16px; background: #d0d0d0;
        }
        .actions-bar .btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 10px 24px; border: none; border-radius: 6px;
            font-size: 14px; font-weight: 600; cursor: pointer;
            text-decoration: none; margin: 0 6px; transition: all 0.2s;
            font-family: 'Segoe UI', sans-serif;
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
            .invoice-page { border: 2px solid #000; margin: 0; }
        }
    </style>
</head>
<body>

<div class="invoice-page" id="invoicePage">

    <!-- TOP HEADER -->
    <div class="inv-top-header">
        <div class="left">
            GST NO:<strong>33AAAPY1027F1Z3</strong>
        </div>
        <div class="center">
            <div class="title-label"><?php echo $page_title; ?></div>
            <div class="company-name">DELVIN DIAMOND TOOL INDUSTRIES</div>
            <div class="addr">1/56, EASU STREET, SOMARASAMPETTAI (PO),</div>
            <div class="addr">TRICHY - 620 102.</div>
            <div class="addr">Email: delvinvincent@yahoo.com</div>
        </div>
        <div class="right">
            <div class="hsn">HSN CODE:68042110</div>
            <div>0431-2607224 (O)</div>
            <div>0431-2607524 (R)</div>
            <div>098424 07224</div>
        </div>
    </div>

    <!-- TO + META DETAILS -->
    <div class="inv-details">
        <div class="to-section">
            <div class="label">TO</div>
            <p class="cname">M/S.<?php echo htmlspecialchars($company_name); ?></p>
            <?php if ($address): ?><p><?php echo htmlspecialchars($address); ?></p><?php endif; ?>
            <?php if ($district): ?><p><?php echo htmlspecialchars($district); ?></p><?php endif; ?>
            <?php if ($state): ?><p><?php echo htmlspecialchars($state); ?></p><?php endif; ?>
        </div>
        <div class="meta-section">
            <div class="meta-row"><div class="mk">PURCHASE ORDER</div><div class="mv"></div></div>
            <div class="meta-row"><div class="mk">DATE:</div><div class="mv"></div></div>
            <div class="meta-row"><div class="mk">DELIVERY CHALLAN No.</div><div class="mv"><?php echo htmlspecialchars($delivery_challan); ?></div></div>
            <div class="meta-row"><div class="mk">INVOICE NO</div><div class="mv"><?php echo $bill; ?></div></div>
            <div class="meta-row"><div class="mk">DATE:</div><div class="mv"><?php echo htmlspecialchars($date_short); ?></div></div>
            <div class="meta-row"><div class="mk">DESPATCH PARTICULARS</div><div class="mv"><?php echo htmlspecialchars($despatch); ?></div></div>
        </div>
    </div>

    <!-- GST ROW -->
    <div class="gst-row">
        <div><span class="label">GST NO.</span> &nbsp; <?php echo htmlspecialchars($gst_no); ?></div>
        <div><span class="label">STATE&amp;CODE</span> &nbsp; <?php echo htmlspecialchars($state); ?> - <?php echo htmlspecialchars($state_code); ?></div>
    </div>

    <!-- LETTER TEXT -->
    <div class="letter-text">
        <strong>Dear Sir,</strong><br>
        &nbsp;&nbsp;&nbsp;&nbsp;This is our invoice in respect of supplies made to you against your above order. The Parcel has been sent by Post parcel to you direct / by V.P.P / through your bankers payable at sight and we request you to favour us with your remittance at an early date / honour the documents on presentation.
    </div>

    <!-- ITEMS TABLE -->
    <table class="inv-items">
        <thead>
            <tr>
                <th style="width:40px;">S.No</th>
                <th>DESCRIPTION</th>
                <th style="width:90px;">QUANTITY</th>
                <th colspan="2" style="width:100px;">RATE<br><small>Rs. &nbsp; P.</small></th>
                <th colspan="2" style="width:120px;">AMOUNT<br><small>Rs. &nbsp; P.</small></th>
            </tr>
        </thead>
        <tbody>
        <?php
        $sno = 1;
        $running_total = 0;
        $taxable_parts = [];

        if (count($items) > 0):
            foreach ($items as $it):
                $gross = $it['qty'] * $it['rate'];
                $disc_amt = 0;
                $net = $gross;
                if ($it['discount_pct'] > 0) {
                    $disc_amt = $gross * $it['discount_pct'] / 100;
                    $net = $gross - $disc_amt;
                }
                $running_total += $net;
                $taxable_parts[] = $net;
        ?>
            <tr>
                <td class="text-center"><?php echo $sno++; ?></td>
                <td><?php echo htmlspecialchars(strtoupper($it['tool_name'])); ?></td>
                <td class="text-center"><?php echo $it['qty']; ?> NOS</td>
                <td class="text-right"><?php echo number_format(floor($it['rate']), 0); ?></td>
                <td class="text-right"><?php echo str_pad(round(($it['rate'] - floor($it['rate'])) * 100), 2, '0', STR_PAD_LEFT); ?></td>
                <td class="text-right"><?php echo number_format(floor($gross), 0); ?></td>
                <td class="text-right"><?php echo str_pad(round(($gross - floor($gross)) * 100), 2, '0', STR_PAD_LEFT); ?></td>
            </tr>
            <?php if ($it['discount_pct'] > 0): ?>
            <tr class="discount-row">
                <td></td>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;DISCOUNT - <?php echo $it['discount_pct']; ?>%</td>
                <td></td>
                <td></td><td></td>
                <td class="text-right"><?php echo number_format(floor($disc_amt), 0); ?></td>
                <td class="text-right"><?php echo str_pad(round(($disc_amt - floor($disc_amt)) * 100), 2, '0', STR_PAD_LEFT); ?></td>
            </tr>
            <tr>
                <td></td><td></td><td></td><td></td><td></td>
                <td class="text-right"><?php echo number_format(floor($net), 0); ?></td>
                <td class="text-right"><?php echo str_pad(round(($net - floor($net)) * 100), 2, '0', STR_PAD_LEFT); ?></td>
            </tr>
            <?php endif; ?>
        <?php
            endforeach;
        endif;
        ?>

        <!-- TAXABLE AMOUNT ROW -->
        <tr class="taxable-row">
            <td></td>
            <td>TAXABLE AMOUNT<?php
                if (count($taxable_parts) > 1) {
                    echo ' - ' . implode(' + ', array_map(function($v){ return number_format($v, 0); }, $taxable_parts));
                }
            ?></td>
            <td></td><td></td><td></td>
            <td class="text-right" colspan="2" style="font-size:14px;">
                <?php echo number_format(floor($taxable_amount), 0); ?>,<?php echo str_pad(round(($taxable_amount - floor($taxable_amount)) * 100), 2, '0', STR_PAD_LEFT); ?>
            </td>
        </tr>

        <!-- BLANK SPACER -->
        <tr><td colspan="7" style="height:10px;"></td></tr>

        <!-- TAX ROWS -->
        <?php if ($gst_type === 'tngst'): ?>
        <tr class="tax-row">
            <td></td>
            <td>CGST - 9%</td>
            <td></td><td></td><td></td>
            <td class="text-right" colspan="2"><?php echo number_format($cgst, 2); ?></td>
        </tr>
        <tr class="tax-row">
            <td></td>
            <td>SGST - 9%</td>
            <td></td><td></td><td></td>
            <td class="text-right" colspan="2"><?php echo number_format($sgst, 2); ?></td>
        </tr>
        <?php else: ?>
        <tr class="tax-row">
            <td></td>
            <td>IGST - 18%</td>
            <td></td><td></td><td></td>
            <td class="text-right" colspan="2"><?php echo number_format($igst, 2); ?></td>
        </tr>
        <?php endif; ?>

        <!-- TOTAL ROW -->
        <tr class="total-row">
            <td></td>
            <td></td>
            <td></td><td></td><td></td>
            <td class="text-right" style="font-size:13px;">TOTAL</td>
            <td class="text-right"><?php echo number_format($total, 2); ?></td>
        </tr>
        </tbody>
    </table>

    <!-- BOTTOM SECTION -->
    <div class="inv-bottom">
        <div class="left-bottom">
            <div class="amount-words">
                RUPEES <?php echo numToWords($total); ?> ONLY.
            </div>
        </div>
        <div class="right-bottom">
            <div class="for-text">for DELVIN DIAMOND TOOL INDUSTRIES</div>
            <div class="sign-line">PROPRIETOR / MANAGER</div>
        </div>
    </div>

    <!-- BANK BAR -->
    <div class="bank-bar">
        BANK NAME : UCO BANK, SOMARASAMPETTAI A/C NO. 07640500000016, IFSC CODE : UCBA0000764
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
        margin: [5, 5, 5, 5],
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