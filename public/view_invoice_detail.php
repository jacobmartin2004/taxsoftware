<?php
require_once '../src/auth.php';
require_once '../src/conn.php';

$type = isset($_GET['type']) ? $_GET['type'] : 'sales';
$bill = isset($_GET['bill']) ? intval($_GET['bill']) : 0;

if ($bill <= 0) { header('Location: view_invoices.php'); exit(); }

$table = ($type === 'purchase') ? 'purchase' : 'delvin';
$page_title = ($type === 'purchase') ? 'PURCHASE INVOICE' : 'TAX INVOICE';

if ($type === 'purchase') {
    $stmt = $conn->prepare("SELECT GSTNO, cname, bill, taxamt, cgst, sgst, igst, Total, date FROM `purchase` WHERE bill = ?");
} else {
    $stmt = $conn->prepare("SELECT GSTNO, cname, bill, taxamt, cgst, sgst, igst, Total, date, challan_no, challan_date FROM `delvin` WHERE bill = ?");
}
if (!$stmt) { echo "<p style='padding:40px;font-family:sans-serif;'>DB Error: " . htmlspecialchars($conn->error) . "</p>"; exit(); }
$stmt->bind_param('i', $bill);
$stmt->execute();
$result = $stmt->get_result();
$inv = $result->fetch_assoc();
$stmt->close();

if (!$inv) { echo "<p style='padding:40px;font-family:sans-serif;'>Invoice not found.</p>"; exit(); }

// Get company address
$address = '';
$gst_type_display = '';
$cstmt = $conn->prepare("SELECT address, gsttype FROM companydata WHERE gstno = ?");
$cstmt->bind_param('s', $inv['GSTNO']);
$cstmt->execute();
$cres = $cstmt->get_result();
if ($crow = $cres->fetch_assoc()) {
    $address = $crow['address'] ?? '';
    $gst_type_display = strtoupper($crow['gsttype'] ?? '');
}
$cstmt->close();

// Get challan info
$challan_no = isset($inv['challan_no']) ? intval($inv['challan_no']) : 0;
$challan_date = isset($inv['challan_date']) ? $inv['challan_date'] : '';

// Fetch invoice items
$items = [];
$inv_type_key = ($type === 'purchase') ? 'purchase' : 'invoice';
$check_table = $conn->query("SHOW TABLES LIKE 'invoice_items'");
if ($check_table && $check_table->num_rows > 0) {
    $istmt = $conn->prepare("SELECT tool_name, qty, rate, discount_pct FROM invoice_items WHERE bill = ? AND invoice_type = ?");
    if ($istmt) {
        $istmt->bind_param('is', $bill, $inv_type_key);
        $istmt->execute();
        $ires = $istmt->get_result();
        while ($irow = $ires->fetch_assoc()) {
            $items[] = $irow;
        }
        $istmt->close();
    }
}

// Determine GST labels based on gst_type
$gst_type_lc = strtolower($gst_type_display);
$cgst_label = 'CGST (9%)'; $sgst_label = 'SGST (9%)';
if ($gst_type_lc === '25p') { $cgst_label = 'CGST (12.5%)'; $sgst_label = 'SGST (12.5%)'; }
elseif ($gst_type_lc === '6p') { $cgst_label = 'CGST (3%)'; $sgst_label = 'SGST (3%)'; }

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
    <title><?php echo $page_title; ?> - #<?php echo $inv['bill']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        @page { size: A4; margin: 12mm 10mm 12mm 18mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #e9ecef; color: #000; }
        .invoice-page {
            --pad-left: 50px;
            --pad-right: 20px;
            width: 182mm; min-height: 271mm; margin: 0 auto; background: #fff;
            padding: 14mm 0 10mm; border: 2px solid #000; position: relative;
            display: flex; flex-direction: column;
        }

        /* Header */
        .inv-header {
            padding: 16px var(--pad-right) 12px var(--pad-left); border-bottom: 2px solid #000;
            display: flex; justify-content: space-between; align-items: center;
        }
        .inv-header .brand h2 { font-size: 22px; font-weight: 900; letter-spacing: 1px; margin-bottom: 4px; color: #000; }
        .inv-header .brand p { font-size: 11px; color: #000; line-height: 1.55; }
        .inv-header .inv-type {
            border: 2px solid #000; color: #000; font-weight: 800;
            padding: 7px 18px; font-size: 15px; letter-spacing: 1px; white-space: nowrap;
        }

        /* Meta rows with borders */
        .inv-meta-table { width: 100%; border-collapse: collapse; }
        .inv-meta-table td {
            border: 1px solid #000; padding: 7px 14px; font-size: 13px; color: #000;
        }
        .inv-meta-table td span { font-weight: 400; }
        .inv-meta-table td strong { margin-left: 4px; }
        .inv-meta-table .right-cell { text-align: right; }

        /* Info Section (Bill To / Customer GST) */
        .inv-info { padding: 16px var(--pad-right) 16px var(--pad-left); display: flex; justify-content: space-between; border-bottom: 1px solid #000; }
        .inv-info .block h6 { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #000; margin-bottom: 5px; }
        .inv-info .block p { font-size: 13px; margin-bottom: 3px; color: #000; line-height: 1.45; }
        .inv-info .block p strong { color: #000; }

        /* Middle content grows to fill page */
        .inv-body { flex: 1; display: flex; flex-direction: column; }

        /* Items Table */
        .inv-table { padding: 0 var(--pad-right) 0 var(--pad-left); margin-top: 12px; }
        .inv-table table { width: 100%; border-collapse: collapse; border: 1px solid #000; }
        .inv-table thead th {
            background: #fff; color: #000; padding: 8px 10px;
            font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; text-align: left;
            border-bottom: 2px solid #000; border-right: 1px solid #000;
        }
        .inv-table thead th:last-child { text-align: right; border-right: none; }
        .inv-table thead th.text-center { text-align: center; }
        .inv-table tbody td { padding: 9px 10px; font-size: 13px; border-bottom: 1px solid #000; border-right: 1px solid #000; color: #000; }
        .inv-table tbody td:last-child { text-align: right; border-right: none; }
        .inv-table tbody td.text-center { text-align: center; }
        .inv-table .discount-sub { color: #000; font-size: 12px; font-style: italic; }

        /* Summary */
        .inv-summary { padding: 12px var(--pad-right) 10px var(--pad-left); display: flex; justify-content: flex-end; }
        .summary-box { width: 330px; border: 1px solid #000; overflow: hidden; }
        .summary-row { display: flex; justify-content: space-between; padding: 8px 14px; font-size: 13px; border-bottom: 1px solid #000; color: #000; }
        .summary-row.total { border-top: 2px solid #000; background: #fff; color: #000; font-weight: 800; font-size: 16px; border-bottom: none; padding: 11px 14px; }
        .summary-words { padding: 10px 14px; border-top: 1px solid #000; font-size: 12px; line-height: 1.5; color: #000; font-weight: 700; }

        /* Spacer pushes footer down */
        .inv-spacer { flex: 1; }

        /* Footer Section - bottom of page */
        .inv-footer-section { margin-top: auto; }
        .inv-words-bank { padding: 8px var(--pad-right) 8px var(--pad-left); border-top: 1px solid #000; font-size: 12px; color: #000; }
        .inv-words-bank .bank { color: #000; }
        .inv-footer {
            padding: 14px var(--pad-right) 12px var(--pad-left); border-top: 2px solid #000;
            display: flex; justify-content: space-between; align-items: flex-end;
            font-size: 12px; color: #000; min-height: 86px;
        }
        .inv-footer .sign { text-align: right; }
        .inv-footer .sign .line { border-top: 1px solid #000; padding-top: 4px; margin-top: 30px; width: 200px; display: inline-block; }
        .inv-footer .for-company { font-weight: 700; color: #000; }

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
        .btn-back { background: #fff; color: #1a2942; border: 1px solid #d1d5db; }
        .btn-back:hover { background: #f1f5f9; color: #1a2942; }
        .btn-dash { background: #22c55e; color: #fff; }
        .btn-dash:hover { background: #16a34a; color: #fff; }

        @media (max-width: 768px) {
            .invoice-page { --pad-left: 14px; --pad-right: 14px; width: 100%; min-height: auto; margin: 8px; border-width: 1px; }
            .inv-header { flex-direction: column; text-align: center; padding: 14px; gap: 8px; }
            .inv-header .brand h2 { font-size: 16px; }
            .inv-header .brand p { font-size: 10px; }
            .inv-header .inv-type { font-size: 12px; padding: 6px 14px; }
            .inv-meta-table td { font-size: 11px; padding: 4px 8px; }
            .inv-info { flex-direction: column; gap: 10px; padding: 12px 14px; }
            .inv-info .block:last-child { text-align: left !important; }
            .inv-table { padding: 0 10px; }
            .inv-table table { font-size: 11px; }
            .inv-table thead th { padding: 6px 4px; font-size: 9px; }
            .inv-table tbody td { padding: 6px 4px; font-size: 11px; }
            .inv-summary { padding: 10px 14px; justify-content: center; }
            .summary-box { width: 100%; }
            .summary-words { font-size: 11px; }
            .inv-words-bank { padding: 8px 14px; font-size: 10px; }
            .inv-footer { flex-direction: column; gap: 10px; padding: 10px 14px; }
            .inv-footer .sign { text-align: center; }
            .inv-footer .sign .line { width: 160px; }
            .actions-bar { padding: 14px 10px; }
            .actions-bar .btn { font-size: 13px; padding: 10px 16px; margin: 4px; width: calc(50% - 12px); justify-content: center; }
        }

        @media print {
            body { background: #fff; margin: 0; }
            .actions-bar { display: none !important; }
            .invoice-page {
                width: 182mm; min-height: 271mm; border: 2px solid #000;
                margin: 0; box-shadow: none; page-break-after: always;
            }
            .inv-header { display: flex !important; flex-direction: row !important; justify-content: space-between !important; }
            .inv-info { display: flex !important; flex-direction: row !important; justify-content: space-between !important; }
            .inv-info .block:last-child { text-align: right !important; }
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
            <p>Ph: 0431-2607224 | 0431-2607524 | 9842407224</p>
            <p>Email: delvinvincent@yahoo.com</p>
        </div>
        <div class="inv-type"><?php echo $page_title; ?></div>
    </div>

    <!-- Invoice Meta Info - Bordered table rows, right corner -->
    <table class="inv-meta-table">
        <tr>
            <td><span>GSTIN:</span> <strong>33AAAPY1027F1Z3</strong></td>
            <td><span>HSN Code:</span> <strong>68042110</strong></td>
            <td><span>Invoice No:</span> <strong><?php echo htmlspecialchars($inv['bill']); ?></strong></td>
            <td class="right-cell"><span>Date:</span> <strong><?php echo htmlspecialchars($inv['date']); ?></strong></td>
        </tr>
        <?php if ($challan_no > 0): ?>
        <tr>
            <td colspan="2">&nbsp;</td>
            <td><span>D.C. No:</span> <strong><?php echo $challan_no; ?></strong></td>
            <td class="right-cell"><span>D.C. Date:</span> <strong><?php echo htmlspecialchars($challan_date); ?></strong></td>
        </tr>
        <?php endif; ?>
    </table>

    <!-- Bill To / Customer GST -->
    <div class="inv-info">
        <div class="block">
            <h6>Bill To</h6>
            <p><strong><?php echo htmlspecialchars($inv['cname']); ?></strong></p>
            <?php if ($address): ?><p><?php echo htmlspecialchars($address); ?></p><?php endif; ?>
        </div>
        <div class="block" style="text-align:right;">
            <h6>Customer GST</h6>
            <p><strong><?php echo htmlspecialchars($inv['GSTNO']); ?></strong></p>
            <p>Type: <?php echo $gst_type_display; ?></p>
        </div>
    </div>

    <!-- Body content area - grows to fill A4 page -->
    <div class="inv-body">

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
                    <th class="text-center">Taxable Amt</th>
                    <?php if ($inv['cgst'] > 0 || $inv['sgst'] > 0): ?>
                    <th class="text-center"><?php echo $cgst_label; ?></th>
                    <th class="text-center"><?php echo $sgst_label; ?></th>
                    <?php endif; ?>
                    <?php if ($inv['igst'] > 0): ?>
                    <th class="text-center">IGST (18%)</th>
                    <?php endif; ?>
                    <th>Total (&#8377;)</th>
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
                $item_cgst = ($inv['taxamt'] > 0) ? ($net / $inv['taxamt']) * $inv['cgst'] : 0;
                $item_sgst = ($inv['taxamt'] > 0) ? ($net / $inv['taxamt']) * $inv['sgst'] : 0;
                $item_igst = ($inv['taxamt'] > 0) ? ($net / $inv['taxamt']) * $inv['igst'] : 0;
                $item_total = $net + $item_cgst + $item_sgst + $item_igst;
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
                    <td class="text-center"><?php echo number_format($net, 2); ?></td>
                    <?php if ($inv['cgst'] > 0 || $inv['sgst'] > 0): ?>
                    <td class="text-center"><?php echo number_format($item_cgst, 2); ?></td>
                    <td class="text-center"><?php echo number_format($item_sgst, 2); ?></td>
                    <?php endif; ?>
                    <?php if ($inv['igst'] > 0): ?>
                    <td class="text-center"><?php echo number_format($item_igst, 2); ?></td>
                    <?php endif; ?>
                    <td><?php echo number_format($item_total, 2); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- Spacer to push footer to bottom -->
    <div class="inv-spacer"></div>

    <!-- Footer Section - pinned to bottom -->
    <div class="inv-footer-section">
        <!-- Summary (above amount in words) -->
        <div class="inv-summary">
            <div class="summary-box">
                <div class="summary-row">
                    <span>Taxable Amount</span>
                    <span>&#8377; <?php echo number_format($inv['taxamt'], 2); ?></span>
                </div>
                <?php if ($inv['cgst'] > 0 || $inv['sgst'] > 0): ?>
                <div class="summary-row">
                    <span><?php echo $cgst_label; ?></span>
                    <span>&#8377; <?php echo number_format($inv['cgst'], 2); ?></span>
                </div>
                <div class="summary-row">
                    <span><?php echo $sgst_label; ?></span>
                    <span>&#8377; <?php echo number_format($inv['sgst'], 2); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($inv['igst'] > 0): ?>
                <div class="summary-row">
                    <span>IGST (18%)</span>
                    <span>&#8377; <?php echo number_format($inv['igst'], 2); ?></span>
                </div>
                <?php endif; ?>
                <div class="summary-row total">
                    <span>TOTAL</span>
                    <span>&#8377; <?php echo number_format($inv['Total'], 2); ?></span>
                </div>
                <div class="summary-words">
                    Rupees in words: RUPEES <?php echo numToWords($inv['Total']); ?> ONLY.
                </div>
            </div>
        </div>

        <!-- Bank Details -->
        <div class="inv-words-bank">
            <p class="bank">BANK: UCO BANK, SOMARASAMPETTAI | A/C: 07640500000016 | IFSC: UCBA0000764</p>
        </div>

        <!-- Footer -->
        <div class="inv-footer">
            <div>
                <p>E. & O. E.</p>
            </div>
            <div class="sign">
                <p class="for-company">for DELVIN DIAMOND TOOL INDUSTRIES</p>
                <div class="line">Proprietor / Manager</div>
            </div>
        </div>
    </div>

    </div><!-- end inv-body -->
</div>

<!-- Action Buttons -->
<div class="actions-bar">
    <button class="btn btn-print" onclick="window.print()"><i class="bi bi-printer"></i> Print Invoice</button>
    <!-- <button class="btn btn-download" onclick="downloadPDF()"><i class="bi bi-download"></i> Download PDF</button> -->
    <?php if ($type === 'purchase'): ?>
    <a href="view_purchases.php" class="btn btn-back"><i class="bi bi-arrow-left"></i> Back to List</a>
    <?php else: ?>
    <a href="view_invoices.php" class="btn btn-back"><i class="bi bi-arrow-left"></i> Back to List</a>
    <?php endif; ?>
    <a href="../index.php" class="btn btn-dash"><i class="bi bi-grid-1x2"></i> Dashboard</a>
</div>

<script>
function downloadPDF() {
    window.scrollTo(0, 0);
    var element = document.getElementById('invoicePage');

    // Clone into a controlled-width container so media queries don't break layout
    var wrapper = document.createElement('div');
    wrapper.style.position = 'fixed';
    wrapper.style.left = '0';
    wrapper.style.top = '0';
    wrapper.style.width = '800px';
    wrapper.style.height = '1px';
    wrapper.style.overflow = 'hidden';
    wrapper.style.zIndex = '-9999';
    wrapper.style.opacity = '0.01';

    var clone = element.cloneNode(true);
    clone.style.width = '780px';
    clone.style.maxWidth = '780px';
    clone.style.margin = '0';
    clone.style.border = '2px solid #000';
    clone.style.background = '#fff';
    // Force desktop flex layout on cloned header/sections
    var flexSections = clone.querySelectorAll('.inv-header, .inv-info, .inv-footer');
    for (var i = 0; i < flexSections.length; i++) {
        flexSections[i].style.display = 'flex';
        flexSections[i].style.flexDirection = 'row';
        flexSections[i].style.justifyContent = 'space-between';
    }
    var infoBlocks = clone.querySelectorAll('.inv-info .block:last-child');
    for (var j = 0; j < infoBlocks.length; j++) {
        infoBlocks[j].style.textAlign = 'right';
    }
    var summaryWrap = clone.querySelectorAll('.inv-summary');
    for (var k = 0; k < summaryWrap.length; k++) {
        summaryWrap[k].style.display = 'flex';
        summaryWrap[k].style.justifyContent = 'flex-end';
    }

    wrapper.appendChild(clone);
    document.body.appendChild(wrapper);

    var opt = {
        margin: [10, 10, 10, 10],
        filename: '<?php echo $page_title; ?>_<?php echo $inv['bill']; ?>_<?php echo preg_replace('/[^A-Za-z0-9]/', '_', $inv['cname']); ?>.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, scrollX: 0, scrollY: 0, windowWidth: 800, height: clone.scrollHeight },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };
    html2pdf().set(opt).from(clone).save().then(function() {
        document.body.removeChild(wrapper);
    });
}
</script>

</body>
</html>
