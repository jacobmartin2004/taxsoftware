<?php
require_once '../src/conn.php';

// Handle delete
if (isset($_GET['delete_bill'])) {
    $del_bill = intval($_GET['delete_bill']);
    $stmt = $conn->prepare("DELETE FROM purchase WHERE bill = ?");
    $stmt->bind_param('i', $del_bill);
    $stmt->execute();
    $stmt->close();
    $conn->query("DELETE FROM invoice_items WHERE bill = $del_bill AND invoice_type = 'purchase'");
    header("Location: view_purchases.php?month=" . ($_GET['month'] ?? date('m')) . "&year=" . ($_GET['year'] ?? date('Y')));
    exit();
}

$sel_month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
$sel_year = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));

$month_str = str_pad($sel_month, 2, '0', STR_PAD_LEFT);
$year_str = strval($sel_year);

$records = [];
$res = $conn->query("SELECT GSTNO, cname, bill, taxamt, cgst, sgst, igst, Total, date FROM purchase WHERE SUBSTRING(date,4,2)='$month_str' AND SUBSTRING(date,7,4)='$year_str' ORDER BY bill DESC");
if ($res) { while ($row = $res->fetch_assoc()) $records[] = $row; }

// Fetch items for all bills in this month
$all_items = [];
$check_table = $conn->query("SHOW TABLES LIKE 'invoice_items'");
$has_items_table = ($check_table && $check_table->num_rows > 0);
if ($has_items_table && count($records) > 0) {
    $bill_nums = array_map(function($r) { return intval($r['bill']); }, $records);
    $bill_list = implode(',', $bill_nums);
    $ires = $conn->query("SELECT bill, tool_name, qty, rate, discount_pct FROM invoice_items WHERE bill IN ($bill_list) AND invoice_type = 'purchase' ORDER BY id ASC");
    if ($ires) {
        while ($irow = $ires->fetch_assoc()) {
            $all_items[intval($irow['bill'])][] = $irow;
        }
    }
}

$month_names = ['','January','February','March','April','May','June','July','August','September','October','November','December'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Invoices - <?php echo $month_names[$sel_month] . ' ' . $sel_year; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        :root { --primary: #1a2942; --accent: #e8a838; --bg: #f1f5f9; --text: #1e293b; --text-muted: #64748b; }
        body { background: var(--bg); font-family: 'Segoe UI', system-ui, sans-serif; color: var(--text); }
        .page-wrap { max-width: 1100px; margin: 0 auto; padding: 20px; }
        .page-header {
            background: #7c3aed; color: #fff; border-radius: 12px;
            padding: 20px 28px; margin-bottom: 24px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .page-header h3 { margin: 0; font-weight: 700; font-size: 20px; }
        .page-header .back-btn {
            color: #fff; text-decoration: none; font-size: 14px;
            display: inline-flex; align-items: center; gap: 6px; opacity: 0.8;
        }
        .page-header .back-btn:hover { opacity: 1; color: #fff; }
        .filter-bar {
            background: #fff; border-radius: 10px; padding: 16px 20px;
            margin-bottom: 20px; border: 1px solid #e2e8f0;
            display: flex; gap: 12px; align-items: end; flex-wrap: wrap;
        }
        .filter-bar .fg { display: flex; flex-direction: column; gap: 4px; }
        .filter-bar label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); }
        .filter-bar select { padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background: #f8fafc; }
        .filter-bar .btn-filter {
            padding: 8px 20px; background: #7c3aed; color: #fff;
            border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 14px;
        }
        .filter-bar .btn-filter:hover { background: #6d28d9; }
        .inv-table-wrap { background: #fff; border-radius: 10px; border: 1px solid #e2e8f0; overflow: hidden; }
        .inv-table-wrap table { width: 100%; border-collapse: collapse; }
        .inv-table-wrap thead th {
            background: #7c3aed; color: #fff; padding: 12px 16px;
            font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; text-align: left;
        }
        .inv-table-wrap tbody td {
            padding: 12px 16px; font-size: 14px; border-bottom: 1px solid #f1f5f9; vertical-align: middle;
        }
        .inv-table-wrap tbody tr:hover { background: #f8fafc; }
        .badge-tngst { background: #dbeafe; color: #1d4ed8; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .badge-igst { background: #fef3c7; color: #b45309; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .view-btn {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 6px 14px; background: var(--accent); color: var(--primary);
            border: none; border-radius: 5px; font-size: 12px; font-weight: 700;
            text-decoration: none; cursor: pointer; transition: all 0.2s;
        }
        .view-btn:hover { background: #d4952e; color: #fff; }
        .inv-btn {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 6px 14px; background: #22c55e; color: #fff;
            border: none; border-radius: 5px; font-size: 12px; font-weight: 700;
            text-decoration: none; transition: all 0.2s;
        }
        .inv-btn:hover { background: #16a34a; color: #fff; }
        .del-btn {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 6px 14px; background: #ef4444; color: #fff;
            border: none; border-radius: 5px; font-size: 12px; font-weight: 700;
            text-decoration: none; cursor: pointer; transition: all 0.2s;
        }
        .del-btn:hover { background: #dc2626; color: #fff; }
        .dl-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 20px; background: #3b82f6; color: #fff;
            border: none; border-radius: 6px; font-size: 14px; font-weight: 600;
            cursor: pointer; text-decoration: none; transition: all 0.2s;
        }
        .dl-btn:hover { background: #2563eb; color: #fff; }
        .total-bar {
            background: #f8fafc; padding: 14px 16px;
            display: flex; justify-content: space-between; font-weight: 700;
            border-top: 2px solid #7c3aed;
        }
        .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
        .empty-state i { font-size: 48px; display: block; margin-bottom: 12px; opacity: 0.4; }
        .empty-state p { font-size: 16px; }
        .top-nav { background: var(--primary); padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; }
        .top-nav a { color: rgba(255,255,255,0.8); text-decoration: none; font-size: 14px; padding: 6px 14px; border-radius: 6px; transition: all 0.2s; }
        .top-nav a:hover, .top-nav a.active { background: rgba(255,255,255,0.1); color: #fff; }
        .top-nav .brand { color: var(--accent); font-weight: 700; font-size: 15px; }
        .top-nav .nav-links { display: flex; gap: 4px; flex-wrap: wrap; }
        .top-nav .menu-toggle { display: none; background: none; border: none; color: #fff; font-size: 24px; cursor: pointer; }

        /* Item detail row */
        .item-detail-row td { padding: 0 !important; border-bottom: 2px solid #7c3aed !important; }
        .item-detail-box {
            background: #f8fafc; padding: 16px 24px; border-top: 1px dashed #d1d5db;
        }
        .item-detail-box h6 { font-size: 13px; font-weight: 700; color: #7c3aed; margin-bottom: 10px; }
        .items-tbl { width: 100%; border-collapse: collapse; border: 1px solid #d1d5db; background: #fff; }
        .items-tbl th {
            background: #e2e8f0; color: var(--text); padding: 8px 12px;
            font-size: 11px; text-transform: uppercase; text-align: left;
            border-bottom: 2px solid #d1d5db;
        }
        .items-tbl th.text-center { text-align: center; }
        .items-tbl th:last-child { text-align: right; }
        .items-tbl td { padding: 8px 12px; font-size: 13px; border-bottom: 1px solid #e2e8f0; color: var(--text); }
        .items-tbl td.text-center { text-align: center; }
        .items-tbl td:last-child { text-align: right; }
        .items-tbl .disc-note { color: #888; font-size: 11px; font-style: italic; }
        .no-items-msg { color: var(--text-muted); font-size: 13px; font-style: italic; padding: 8px 0; }

        @media (max-width: 768px) {
            .top-nav { flex-wrap: wrap; padding: 10px 16px; }
            .top-nav .menu-toggle { display: block; }
            .top-nav .nav-links { display: none; width: 100%; flex-direction: column; padding-top: 10px; }
            .top-nav .nav-links.show { display: flex; }
            .top-nav .nav-links a { padding: 10px 14px; border-bottom: 1px solid rgba(255,255,255,0.1); }
            .page-wrap { padding: 10px; }
            .page-header { padding: 14px 16px; margin-bottom: 14px; flex-direction: column; gap: 8px; text-align: center; }
            .page-header h3 { font-size: 16px; }
            .filter-bar { flex-direction: column; padding: 12px; }
            .filter-bar .fg { width: 100%; }
            .filter-bar select { width: 100%; }
            .filter-bar .btn-filter { width: 100%; }
            .inv-table-wrap { overflow-x: auto; }
            .inv-table-wrap table { min-width: 800px; }
            .inv-table-wrap thead th { padding: 10px 8px; font-size: 11px; }
            .inv-table-wrap tbody td { padding: 10px 8px; font-size: 13px; }
            .view-btn, .inv-btn, .del-btn { padding: 6px 10px; font-size: 11px; margin-bottom: 4px; display: inline-block; }
            .dl-btn { width: calc(100% - 20px); justify-content: center; }
            .item-detail-box { padding: 12px 10px; }
        }
    </style>
</head>
<body>
<nav class="top-nav">
    <span class="brand">DELVIN DIAMOND TOOLS</span>
    <button class="menu-toggle" onclick="this.nextElementSibling.classList.toggle('show')"><i class="bi bi-list"></i></button>
    <div class="nav-links">
        <a href="index.php"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
        <a href="create_purchase.php"><i class="bi bi-cart-plus"></i> New Purchase</a>
        <a href="view_invoices.php"><i class="bi bi-graph-up-arrow"></i> Sales Invoices</a>
        <a href="view_purchases.php" class="active"><i class="bi bi-cart-check"></i> Purchase Invoices</a>
        <a href="../src/companydata.php"><i class="bi bi-building"></i> Companies</a>
        <a href="add_tool.php"><i class="bi bi-tools"></i> Tools</a>
    </div>
</nav>
<div class="page-wrap">
    <div class="page-header">
        <h3><i class="bi bi-cart-check me-2"></i>Purchase Invoices</h3>
        <a href="index.php" class="back-btn"><i class="bi bi-arrow-left"></i> Dashboard</a>
    </div>

    <form class="filter-bar" method="GET">
        <div class="fg">
            <label>Month</label>
            <select name="month">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?php echo $m; ?>" <?php echo $m == $sel_month ? 'selected' : ''; ?>><?php echo $month_names[$m]; ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="fg">
            <label>Year</label>
            <select name="year">
                <?php for ($y = 2020; $y <= intval(date('Y')) + 1; $y++): ?>
                <option value="<?php echo $y; ?>" <?php echo $y == $sel_year ? 'selected' : ''; ?>><?php echo $y; ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <button type="submit" class="btn-filter"><i class="bi bi-funnel me-1"></i>Filter</button>
    </form>

    <h5 style="margin-bottom: 16px; color: var(--text-muted); font-size: 14px;">
        Showing <strong style="color: var(--text);">Purchase</strong> invoices for
        <strong style="color: var(--text);"><?php echo $month_names[$sel_month] . ' ' . $sel_year; ?></strong>
        — <?php echo count($records); ?> record(s)
    </h5>

    <div class="inv-table-wrap">
        <?php if (count($records) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Bill No</th>
                    <th>Company</th>
                    <th>GST No</th>
                    <th>Taxable Amt</th>
                    <th>Tax</th>
                    <th>Total</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $grand_total = 0;
            $sno = 1;
            foreach ($records as $r):
                $grand_total += $r['Total'];
                $tax_display = '';
                if ($r['cgst'] > 0 || $r['sgst'] > 0) {
                    $tax_display = '<span class="badge-tngst">CGST+SGST</span>';
                } elseif ($r['igst'] > 0) {
                    $tax_display = '<span class="badge-igst">IGST</span>';
                }
                $bill_id = intval($r['bill']);
                $bill_items = isset($all_items[$bill_id]) ? $all_items[$bill_id] : [];
            ?>
                <tr>
                    <td><?php echo $sno++; ?></td>
                    <td><strong><?php echo htmlspecialchars($r['bill']); ?></strong></td>
                    <td><?php echo htmlspecialchars($r['cname']); ?></td>
                    <td><small><?php echo htmlspecialchars($r['GSTNO']); ?></small></td>
                    <td>₹ <?php echo number_format($r['taxamt'], 2); ?></td>
                    <td><?php echo $tax_display; ?></td>
                    <td><strong>₹ <?php echo number_format($r['Total'], 2); ?></strong></td>
                    <td><?php echo htmlspecialchars($r['date']); ?></td>
                    <td>
                        <button class="view-btn" onclick="toggleItems(<?php echo $bill_id; ?>)">
                            <i class="bi bi-eye"></i> View
                        </button>
                        <a href="view_invoice_detail.php?type=purchase&bill=<?php echo $bill_id; ?>" class="inv-btn">
                            <i class="bi bi-file-earmark-text"></i> Invoice
                        </a>
                        <a href="#" class="del-btn" onclick="confirmDelete(<?php echo $bill_id; ?>); return false;">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
                <!-- Expandable item detail row -->
                <tr class="item-detail-row" id="items-<?php echo $bill_id; ?>" style="display:none;">
                    <td colspan="9">
                        <div class="item-detail-box">
                            <h6><i class="bi bi-tools me-1"></i>Tool Details — Bill #<?php echo $bill_id; ?></h6>
                            <?php if (count($bill_items) > 0): ?>
                            <table class="items-tbl">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Tool Name</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-center">Rate (₹)</th>
                                        <th class="text-center">Discount</th>
                                        <th>Taxable Amt (₹)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $item_sno = 1;
                                foreach ($bill_items as $it):
                                    $gross = $it['qty'] * $it['rate'];
                                    $disc_amt = 0;
                                    $net = $gross;
                                    if ($it['discount_pct'] > 0) {
                                        $disc_amt = $gross * $it['discount_pct'] / 100;
                                        $net = $gross - $disc_amt;
                                    }
                                ?>
                                    <tr>
                                        <td><?php echo $item_sno++; ?></td>
                                        <td>
                                            <?php echo htmlspecialchars(strtoupper($it['tool_name'])); ?>
                                            <?php if ($it['discount_pct'] > 0): ?>
                                            <br><span class="disc-note">Discount: <?php echo $it['discount_pct']; ?>% (-₹<?php echo number_format($disc_amt, 2); ?>)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><?php echo $it['qty']; ?></td>
                                        <td class="text-center"><?php echo number_format($it['rate'], 2); ?></td>
                                        <td class="text-center">
                                            <?php if ($it['discount_pct'] > 0): ?>
                                                <strong><?php echo $it['discount_pct']; ?>%</strong>
                                            <?php else: ?>
                                                <span style="color:#999;">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo number_format($net, 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php else: ?>
                            <p class="no-items-msg"><i class="bi bi-info-circle me-1"></i>No tool details available for this invoice. Only invoices created after the update will show tool details.</p>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div class="total-bar">
            <span>Total (<?php echo count($records); ?> invoices)</span>
            <span>₹ <?php echo number_format($grand_total, 2); ?></span>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <p>No purchase invoices found for <?php echo $month_names[$sel_month] . ' ' . $sel_year; ?></p>
        </div>
        <?php endif; ?>
    </div>

    <?php if (count($records) > 0): ?>
    <div style="text-align:center; margin-top: 20px;">
        <button class="dl-btn" onclick="downloadMonthlyPDF()"><i class="bi bi-file-earmark-pdf"></i> Download <?php echo $month_names[$sel_month] . ' ' . $sel_year; ?> as PDF</button>
    </div>
    <?php endif; ?>
</div>

<?php
$pdf_records = [];
foreach ($records as $r) {
    $addr = ''; $st = ''; $dist = ''; $gtype = '';
    $cs = $conn->prepare("SELECT address, state, district, gsttype FROM companydata WHERE gstno = ?");
    $cs->bind_param('s', $r['GSTNO']);
    $cs->execute();
    $cr = $cs->get_result();
    if ($cw = $cr->fetch_assoc()) {
        $addr = $cw['address'] ?? ''; $st = $cw['state'] ?? '';
        $dist = $cw['district'] ?? ''; $gtype = strtoupper($cw['gsttype'] ?? '');
    }
    $cs->close();
    $bill_id = intval($r['bill']);
    $bill_items_arr = isset($all_items[$bill_id]) ? $all_items[$bill_id] : [];
    $items_json = [];
    foreach ($bill_items_arr as $it) {
        $items_json[] = [
            'tool_name' => $it['tool_name'],
            'qty' => intval($it['qty']),
            'rate' => floatval($it['rate']),
            'discount_pct' => floatval($it['discount_pct']),
        ];
    }
    $pdf_records[] = [
        'bill' => $r['bill'], 'cname' => $r['cname'], 'GSTNO' => $r['GSTNO'],
        'taxamt' => floatval($r['taxamt']), 'cgst' => floatval($r['cgst']),
        'sgst' => floatval($r['sgst']), 'igst' => floatval($r['igst']),
        'Total' => floatval($r['Total']), 'date' => $r['date'],
        'address' => $addr, 'state' => $st, 'district' => $dist, 'gsttype' => $gtype,
        'items' => $items_json
    ];
}
?>

<script>
var invoiceData = <?php echo json_encode($pdf_records, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

function toggleItems(billId) {
    var row = document.getElementById('items-' + billId);
    row.style.display = (row.style.display === 'none') ? '' : 'none';
}

function confirmDelete(bill) {
    if (confirm('Are you sure you want to delete purchase invoice #' + bill + '? This cannot be undone.')) {
        window.location.href = 'view_purchases.php?delete_bill=' + bill + '&month=<?php echo $sel_month; ?>&year=<?php echo $sel_year; ?>';
    }
}

function fmt(v) { return Number(v).toLocaleString('en-IN', {minimumFractionDigits:2, maximumFractionDigits:2}); }

function numToWords(n) {
    var ones = ['','ONE','TWO','THREE','FOUR','FIVE','SIX','SEVEN','EIGHT','NINE','TEN',
        'ELEVEN','TWELVE','THIRTEEN','FOURTEEN','FIFTEEN','SIXTEEN','SEVENTEEN','EIGHTEEN','NINETEEN'];
    var tens = ['','','TWENTY','THIRTY','FORTY','FIFTY','SIXTY','SEVENTY','EIGHTY','NINETY'];
    if (n === 0) return 'ZERO';
    var w = '';
    if (Math.floor(n/10000000) > 0) { w += numToWords(Math.floor(n/10000000)) + ' CRORE '; n %= 10000000; }
    if (Math.floor(n/100000) > 0) { w += numToWords(Math.floor(n/100000)) + ' LAKH '; n %= 100000; }
    if (Math.floor(n/1000) > 0) { w += numToWords(Math.floor(n/1000)) + ' THOUSAND '; n %= 1000; }
    if (Math.floor(n/100) > 0) { w += ones[Math.floor(n/100)] + ' HUNDRED '; n %= 100; }
    if (n > 0) { if (w !== '') w += 'AND '; if (n < 20) { w += ones[n]; } else { w += tens[Math.floor(n/10)]; if (n%10) w += ' ' + ones[n%10]; } }
    return w.trim();
}

function buildInvoiceHTML(r) {
    var loc = (r.district || '') + (r.district && r.state ? ', ' : '') + (r.state || '');
    var taxRows = '';
    var cgst_label = 'CGST (9%)', sgst_label = 'SGST (9%)';
    if (r.gsttype === '25P') { cgst_label = 'CGST (12.5%)'; sgst_label = 'SGST (12.5%)'; }
    else if (r.gsttype === '6P') { cgst_label = 'CGST (3%)'; sgst_label = 'SGST (3%)'; }

    if (r.cgst > 0 || r.sgst > 0) {
        taxRows = '<div class="sr"><span>' + cgst_label + '</span><span>₹ ' + fmt(r.cgst) + '</span></div>' +
                  '<div class="sr"><span>' + sgst_label + '</span><span>₹ ' + fmt(r.sgst) + '</span></div>';
    }
    if (r.igst > 0) {
        taxRows = '<div class="sr"><span>IGST (18%)</span><span>₹ ' + fmt(r.igst) + '</span></div>';
    }

    var itemsHTML = '';
    if (r.items && r.items.length > 0) {
        itemsHTML = '<table class="itbl"><thead><tr><th>#</th><th>Tool Name</th><th style="text-align:center">Qty</th><th style="text-align:center">Rate</th><th style="text-align:center">Discount</th><th style="text-align:right">Taxable Amt</th></tr></thead><tbody>';
        for (var i = 0; i < r.items.length; i++) {
            var it = r.items[i];
            var gross = it.qty * it.rate;
            var disc = it.discount_pct > 0 ? (gross * it.discount_pct / 100) : 0;
            var net = gross - disc;
            var discStr = it.discount_pct > 0 ? it.discount_pct + '%' : '—';
            itemsHTML += '<tr><td>' + (i+1) + '</td><td>' + it.tool_name.toUpperCase() + (it.discount_pct > 0 ? '<br><span style="color:#888;font-size:11px;font-style:italic">Disc: ' + it.discount_pct + '% (-₹' + fmt(disc) + ')</span>' : '') + '</td><td style="text-align:center">' + it.qty + '</td><td style="text-align:center">' + fmt(it.rate) + '</td><td style="text-align:center">' + discStr + '</td><td style="text-align:right">' + fmt(net) + '</td></tr>';
        }
        itemsHTML += '</tbody></table>';
    }

    return '<div class="inv-pg">' +
        '<div class="hdr"><div class="br">' +
            '<h2>DELVIN DIAMOND TOOL INDUSTRIES</h2>' +
            '<p>1/56, Easu Street, Somarasampettai (PO), Trichy - 620 102</p>' +
            '<p>Ph: 0431-2607224 | 0431-2607524 | 9842407224</p>' +
            '<p>Email: delvinvincent@yahoo.com</p>' +
        '</div><div class="inv-tp">PURCHASE INVOICE</div></div>' +
        '<div class="gbar">' +
            '<div><span>GSTIN:</span> <b>33AAAPY1027F1Z3</b></div>' +
            '<div><span>HSN Code:</span> <b>68042110</b></div>' +
            '<div><span>Invoice #:</span> <b>' + r.bill + '</b></div>' +
            '<div><span>Date:</span> <b>' + r.date + '</b></div>' +
        '</div>' +
        '<div class="iinfo">' +
            '<div class="blk"><h6>Bill To</h6><p><b>M/S. ' + r.cname + '</b></p>' +
            (r.address ? '<p>' + r.address + '</p>' : '') +
            '<p>' + loc + '</p></div>' +
            '<div class="blk" style="text-align:right;"><h6>Customer GST</h6><p><b>' + r.GSTNO + '</b></p><p>Type: ' + r.gsttype + '</p></div>' +
        '</div>' +
        (itemsHTML ? '<div class="items-sec">' + itemsHTML + '</div>' : '') +
        '<div class="sbox-wrap"><div class="sbox">' +
            '<div class="sr"><span>Taxable Amount</span><span>₹ ' + fmt(r.taxamt) + '</span></div>' +
            taxRows +
            '<div class="sr stotal"><span>TOTAL</span><span>₹ ' + fmt(r.Total) + '</span></div>' +
        '</div></div>' +
        '<div class="wbank">' +
            '<p class="wds">Amount in words: RUPEES ' + numToWords(Math.round(r.Total)) + ' ONLY.</p>' +
            '<p class="bnk">BANK: UCO BANK, SOMARASAMPETTAI | A/C: 07640500000016 | IFSC: UCBA0000764</p>' +
        '</div>' +
        '<div class="ftr"><div><p>E. & O. E.</p></div>' +
            '<div class="sgn"><p class="fc">for DELVIN DIAMOND TOOL INDUSTRIES</p>' +
            '<div class="sline">Proprietor / Manager</div></div>' +
        '</div>' +
    '</div>';
}

function downloadMonthlyPDF() {
    var css = '<style>' +
        '.inv-pg { width:750px; background:#fff; border:2px solid #000; margin:0 auto; page-break-after:always; font-family:Segoe UI,Arial,sans-serif; color:#000; font-size:13px; }' +
        '.inv-pg:last-child { page-break-after: auto; }' +
        '.hdr { padding:20px 30px; border-bottom:2px solid #000; display:flex; justify-content:space-between; align-items:center; }' +
        '.hdr .br p { font-size:11px; margin:0; color:#000; }' +
        '.hdr .br h2 { margin:0; font-size:22px; font-weight:900; color:#000; }' +
        '.inv-tp { border:2px solid #000; color:#000; font-weight:800; padding:8px 20px; font-size:14px; letter-spacing:1px; white-space:nowrap; }' +
        '.gbar { padding:10px 30px; border-bottom:1px solid #000; display:flex; justify-content:space-between; font-size:12px; color:#000; }' +
        '.gbar span { color:#000; } .gbar b { color:#000; }' +
        '.iinfo { padding:20px 30px; display:flex; justify-content:space-between; border-bottom:1px solid #000; }' +
        '.iinfo .blk h6 { font-size:10px; text-transform:uppercase; letter-spacing:1px; color:#000; font-weight:700; margin:0 0 6px; }' +
        '.iinfo .blk p { font-size:13px; margin:0 0 2px; color:#000; } .iinfo .blk b { color:#000; }' +
        '.items-sec { padding:0 30px; }' +
        '.itbl { width:100%; border-collapse:collapse; border:1px solid #000; margin-top:10px; }' +
        '.itbl th { background:#fff; color:#000; padding:8px 10px; font-size:11px; text-transform:uppercase; border-bottom:2px solid #000; border-right:1px solid #000; text-align:left; }' +
        '.itbl th:last-child { border-right:none; }' +
        '.itbl td { padding:8px 10px; font-size:13px; border-bottom:1px solid #ccc; border-right:1px solid #000; color:#000; }' +
        '.itbl td:last-child { border-right:none; }' +
        '.sbox-wrap { padding:10px 30px 16px; display:flex; justify-content:flex-end; }' +
        '.sbox { width:300px; border:1px solid #000; }' +
        '.sr { display:flex; justify-content:space-between; padding:6px 14px; font-size:13px; border-bottom:1px solid #ccc; color:#000; }' +
        '.sr.stotal { font-weight:800; font-size:15px; border-top:2px solid #000; border-bottom:none; padding:10px 14px; color:#000; }' +
        '.wbank { padding:10px 30px; border-top:1px solid #000; font-size:12px; color:#000; }' +
        '.wds { font-weight:700; margin:0 0 8px; } .bnk { color:#000; margin:0; }' +
        '.ftr { padding:15px 30px; border-top:2px solid #000; display:flex; justify-content:space-between; align-items:flex-end; font-size:11px; color:#000; }' +
        '.sgn { text-align:right; }' +
        '.fc { font-weight:700; color:#000; }' +
        '.sline { border-top:1px solid #000; padding-top:4px; margin-top:30px; width:200px; display:inline-block; }' +
    '</style>';

    var html = css;
    for (var i = 0; i < invoiceData.length; i++) {
        html += buildInvoiceHTML(invoiceData[i]);
    }

    var container = document.createElement('div');
    container.innerHTML = html;
    container.style.position = 'absolute';
    container.style.left = '-9999px';
    container.style.top = '0';
    container.style.width = '800px';
    document.body.appendChild(container);

    var opt = {
        margin: 10,
        filename: 'Purchase_<?php echo $month_names[$sel_month]; ?>_<?php echo $sel_year; ?>.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, scrollX: 0, scrollY: 0, windowWidth: 820 },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
        pagebreak: { mode: ['css'], avoid: '.inv-pg' }
    };

    html2pdf().set(opt).from(container).save().then(function() {
        document.body.removeChild(container);
    });
}
</script>
</body>
</html>
