<?php
require_once '../src/conn.php';

// Handle delete
if (isset($_GET['delete_bill']) && isset($_GET['delete_type'])) {
    $del_bill = intval($_GET['delete_bill']);
    $del_type = $_GET['delete_type'];
    $del_table = ($del_type === 'purchase') ? 'purchase' : 'delvin';
    $stmt = $conn->prepare("DELETE FROM `$del_table` WHERE bill = ?");
    $stmt->bind_param('i', $del_bill);
    $stmt->execute();
    $stmt->close();
    header("Location: view_invoices.php?type=" . urlencode($del_type) . "&month=" . ($_GET['month'] ?? date('m')) . "&year=" . ($_GET['year'] ?? date('Y')));
    exit();
}

// Get selected month/year or default to current
$sel_month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
$sel_year = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));
$sel_type = isset($_GET['type']) ? $_GET['type'] : 'sales';

$month_str = str_pad($sel_month, 2, '0', STR_PAD_LEFT);
$year_str = strval($sel_year);

$records = [];
if ($sel_type === 'sales') {
    $res = $conn->query("SELECT GSTNO, cname, bill, taxamt, cgst, sgst, igst, Total, date FROM delvin WHERE SUBSTRING(date,4,2)='$month_str' AND SUBSTRING(date,7,4)='$year_str' ORDER BY bill DESC");
} else {
    $res = $conn->query("SELECT GSTNO, cname, bill, taxamt, cgst, sgst, igst, Total, date FROM purchase WHERE SUBSTRING(date,4,2)='$month_str' AND SUBSTRING(date,7,4)='$year_str' ORDER BY bill DESC");
}
if ($res) { while ($row = $res->fetch_assoc()) $records[] = $row; }

$month_names = ['','January','February','March','April','May','June','July','August','September','October','November','December'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Invoices - <?php echo $month_names[$sel_month] . ' ' . $sel_year; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1a2942;
            --accent: #e8a838;
            --bg: #f1f5f9;
            --text: #1e293b;
            --text-muted: #64748b;
        }
        body { background: var(--bg); font-family: 'Segoe UI', system-ui, sans-serif; color: var(--text); }

        .page-wrap { max-width: 1100px; margin: 0 auto; padding: 20px; }
        .page-header {
            background: var(--primary); color: #fff; border-radius: 12px;
            padding: 20px 28px; margin-bottom: 24px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .page-header h3 { margin: 0; font-weight: 700; font-size: 20px; }
        .page-header .back-btn {
            color: #fff; text-decoration: none; font-size: 14px;
            display: inline-flex; align-items: center; gap: 6px;
            opacity: 0.8; transition: opacity 0.2s;
        }
        .page-header .back-btn:hover { opacity: 1; color: #fff; }

        /* Filters */
        .filter-bar {
            background: #fff; border-radius: 10px; padding: 16px 20px;
            margin-bottom: 20px; border: 1px solid #e2e8f0;
            display: flex; gap: 12px; align-items: end; flex-wrap: wrap;
        }
        .filter-bar .fg { display: flex; flex-direction: column; gap: 4px; }
        .filter-bar label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); }
        .filter-bar select, .filter-bar input {
            padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;
            font-size: 14px; background: #f8fafc;
        }
        .filter-bar .btn-filter {
            padding: 8px 20px; background: var(--primary); color: #fff;
            border: none; border-radius: 6px; font-weight: 600; cursor: pointer;
            font-size: 14px; transition: background 0.2s;
        }
        .filter-bar .btn-filter:hover { background: #2c3e5a; }

        /* Type Tabs */
        .type-tabs { display: flex; gap: 0; margin-bottom: 20px; }
        .type-tabs a {
            padding: 10px 24px; text-decoration: none; font-weight: 600; font-size: 14px;
            border: 1px solid #e2e8f0; color: var(--text-muted); background: #fff;
            transition: all 0.2s;
        }
        .type-tabs a:first-child { border-radius: 8px 0 0 8px; }
        .type-tabs a:last-child { border-radius: 0 8px 8px 0; border-left: none; }
        .type-tabs a.active { background: var(--primary); color: #fff; border-color: var(--primary); }
        .type-tabs a:hover:not(.active) { background: #f8fafc; }

        /* Table */
        .inv-table-wrap {
            background: #fff; border-radius: 10px; border: 1px solid #e2e8f0; overflow: hidden;
        }
        .inv-table-wrap table { width: 100%; border-collapse: collapse; }
        .inv-table-wrap thead th {
            background: var(--primary); color: #fff; padding: 12px 16px;
            font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; text-align: left;
        }
        .inv-table-wrap tbody td {
            padding: 12px 16px; font-size: 14px; border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .inv-table-wrap tbody tr:hover { background: #f8fafc; }
        .inv-table-wrap tbody tr:last-child td { border-bottom: none; }

        .badge-tngst { background: #dbeafe; color: #1d4ed8; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .badge-igst { background: #fef3c7; color: #b45309; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }

        .view-btn {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 6px 14px; background: var(--accent); color: var(--primary);
            border: none; border-radius: 5px; font-size: 12px; font-weight: 700;
            text-decoration: none; transition: all 0.2s;
        }
        .view-btn:hover { background: #d4952e; color: #fff; }
        .edit-btn {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 6px 14px; background: #3b82f6; color: #fff;
            border: none; border-radius: 5px; font-size: 12px; font-weight: 700;
            text-decoration: none; transition: all 0.2s;
        }
        .edit-btn:hover { background: #2563eb; color: #fff; }
        .del-btn {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 6px 14px; background: #ef4444; color: #fff;
            border: none; border-radius: 5px; font-size: 12px; font-weight: 700;
            text-decoration: none; transition: all 0.2s; cursor: pointer;
        }
        .del-btn:hover { background: #dc2626; color: #fff; }
        .dl-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 20px; background: #3b82f6; color: #fff;
            border: none; border-radius: 6px; font-size: 14px; font-weight: 600;
            cursor: pointer; text-decoration: none; transition: all 0.2s;
        }
        .dl-btn:hover { background: #2563eb; color: #fff; }

        .top-nav { background: var(--primary); padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; }
        .top-nav a { color: rgba(255,255,255,0.8); text-decoration: none; font-size: 14px; padding: 6px 14px; border-radius: 6px; transition: all 0.2s; }
        .top-nav a:hover, .top-nav a.active { background: rgba(255,255,255,0.1); color: #fff; }
        .top-nav .brand { color: var(--accent); font-weight: 700; font-size: 15px; }

        .empty-state {
            text-align: center; padding: 60px 20px; color: var(--text-muted);
        }
        .empty-state i { font-size: 48px; display: block; margin-bottom: 12px; opacity: 0.4; }
        .empty-state p { font-size: 16px; }

        .total-bar {
            background: #f8fafc; padding: 14px 16px;
            display: flex; justify-content: space-between; font-weight: 700;
            border-top: 2px solid var(--primary);
        }

        .top-nav .nav-links { display: flex; gap: 4px; flex-wrap: wrap; }
        .top-nav .menu-toggle { display: none; background: none; border: none; color: #fff; font-size: 24px; cursor: pointer; }

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
            .type-tabs a { padding: 8px 14px; font-size: 12px; }
            .inv-table-wrap { overflow-x: auto; }
            .inv-table-wrap table { min-width: 700px; }
            .inv-table-wrap thead th { padding: 10px 8px; font-size: 11px; }
            .inv-table-wrap tbody td { padding: 10px 8px; font-size: 13px; }
            .view-btn, .edit-btn, .del-btn { padding: 6px 10px; font-size: 11px; margin-bottom: 4px; display: inline-block; }
            .dl-btn { width: calc(100% - 20px); justify-content: center; }
        }
    </style>
</head>
<body>
<nav class="top-nav">
    <span class="brand">DELVIN DIAMOND TOOLS</span>
    <button class="menu-toggle" onclick="this.nextElementSibling.classList.toggle('show')"><i class="bi bi-list"></i></button>
    <div class="nav-links">
        <a href="index.php"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
        <a href="create_invoice.php"><i class="bi bi-receipt"></i> Invoice</a>
        <a href="create_purchase.php"><i class="bi bi-cart-plus"></i> Purchase</a>
        <a href="view_invoices.php" class="active"><i class="bi bi-journal-bookmark-fill"></i> Invoices</a>
        <a href="../src/companydata.php"><i class="bi bi-building"></i> Companies</a>
        <a href="add_tool.php"><i class="bi bi-tools"></i> Tools</a>
    </div>
</nav>
<div class="page-wrap">
    <!-- Header -->
    <div class="page-header">
        <h3><i class="bi bi-journal-bookmark-fill me-2"></i>View Invoices</h3>
        <a href="index.php" class="back-btn"><i class="bi bi-arrow-left"></i> Dashboard</a>
    </div>

    <!-- Type Tabs -->
    <div class="type-tabs">
        <a href="?type=sales&month=<?php echo $sel_month; ?>&year=<?php echo $sel_year; ?>" class="<?php echo $sel_type === 'sales' ? 'active' : ''; ?>">
            <i class="bi bi-graph-up-arrow me-1"></i>Sales Invoices
        </a>
        <a href="?type=purchase&month=<?php echo $sel_month; ?>&year=<?php echo $sel_year; ?>" class="<?php echo $sel_type === 'purchase' ? 'active' : ''; ?>">
            <i class="bi bi-cart-check me-1"></i>Purchase Invoices
        </a>
    </div>

    <!-- Filter Bar -->
    <form class="filter-bar" method="GET">
        <input type="hidden" name="type" value="<?php echo htmlspecialchars($sel_type); ?>">
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

    <!-- Month Title -->
    <h5 style="margin-bottom: 16px; color: var(--text-muted); font-size: 14px;">
        Showing <strong style="color: var(--text);"><?php echo ucfirst($sel_type); ?></strong> invoices for
        <strong style="color: var(--text);"><?php echo $month_names[$sel_month] . ' ' . $sel_year; ?></strong>
        — <?php echo count($records); ?> record(s)
    </h5>

    <!-- Table -->
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
                        <a href="view_invoice_detail.php?type=<?php echo $sel_type; ?>&bill=<?php echo $r['bill']; ?>" class="view-btn">
                            <i class="bi bi-eye"></i> View
                        </a>
                        <a href="edit_invoice.php?type=<?php echo $sel_type; ?>&bill=<?php echo $r['bill']; ?>" class="edit-btn">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <a href="#" class="del-btn" onclick="confirmDelete(<?php echo $r['bill']; ?>); return false;">
                            <i class="bi bi-trash"></i> Delete
                        </a>
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
            <p>No <?php echo $sel_type; ?> invoices found for <?php echo $month_names[$sel_month] . ' ' . $sel_year; ?></p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Download Monthly PDF -->
    <?php if (count($records) > 0): ?>
    <div style="text-align:center; margin-top: 20px;">
        <button class="dl-btn" onclick="downloadMonthlyPDF()"><i class="bi bi-file-earmark-pdf"></i> Download <?php echo $month_names[$sel_month] . ' ' . $sel_year; ?> as PDF</button>
    </div>
    <?php endif; ?>
</div>

<?php
// Build JSON data with company addresses for PDF generation
$pdf_records = [];
foreach ($records as $r) {
    $addr = ''; $st = ''; $dist = ''; $gtype = '';
    $cs = $conn->prepare("SELECT address, state, district, gsttype FROM companydata WHERE gstno = ?");
    $cs->bind_param('s', $r['GSTNO']);
    $cs->execute();
    $cr = $cs->get_result();
    if ($cw = $cr->fetch_assoc()) {
        $addr = $cw['address'] ?? '';
        $st = $cw['state'] ?? '';
        $dist = $cw['district'] ?? '';
        $gtype = strtoupper($cw['gsttype'] ?? '');
    }
    $cs->close();
    $pdf_records[] = [
        'bill' => $r['bill'], 'cname' => $r['cname'], 'GSTNO' => $r['GSTNO'],
        'taxamt' => floatval($r['taxamt']), 'cgst' => floatval($r['cgst']),
        'sgst' => floatval($r['sgst']), 'igst' => floatval($r['igst']),
        'Total' => floatval($r['Total']), 'date' => $r['date'],
        'address' => $addr, 'state' => $st, 'district' => $dist, 'gsttype' => $gtype
    ];
}
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
var invoiceData = <?php echo json_encode($pdf_records, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

function confirmDelete(bill) {
    if (confirm('Are you sure you want to delete invoice #' + bill + '? This cannot be undone.')) {
        window.location.href = 'view_invoices.php?delete_bill=' + bill + '&delete_type=<?php echo urlencode($sel_type); ?>&month=<?php echo $sel_month; ?>&year=<?php echo $sel_year; ?>';
    }
}

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

function fmt(v) { return Number(v).toLocaleString('en-IN', {minimumFractionDigits:2, maximumFractionDigits:2}); }

function buildInvoiceHTML(r, pageTitle) {
    var loc = (r.district ? r.district : '') + (r.district && r.state ? ', ' : '') + (r.state ? r.state : '');
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

    return '<div class="inv-pg">' +
        '<div class="hdr"><div class="br">' +
            '<h2 style="font-size:22px;font-weight:900;letter-spacing:1px;margin-bottom:2px;color:#000;">DELVIN DIAMOND TOOL INDUSTRIES</h2>' +
            '<p>1/56, Easu Street, Somarasampettai (PO), Trichy - 620 102</p>' +
            '<p>Ph: 0431-2607224 | 0431-2607524 | 9842407224</p>' +
            '<p>Email: delvinvincent@yahoo.com</p>' +
        '</div><div class="inv-tp">' + pageTitle + '</div></div>' +
        '<div class="gbar">' +
            '<div><span>GSTIN:</span> <b>33AAAPY1027F1Z3</b></div>' +
            '<div><span>HSN Code:</span> <b>68042110</b></div>' +
            '<div><span>Invoice Number:</span> <b>' + r.bill + '</b></div>' +
            '<div><span>Date:</span> <b>' + r.date + '</b></div>' +
        '</div>' +
        '<div class="iinfo">' +
            '<div class="blk"><h6>Bill To</h6><p><b>M/S. ' + r.cname + '</b></p>' +
            (r.address ? '<p>' + r.address + '</p>' : '') +
            '<p>' + loc + '</p></div>' +
            '<div class="blk" style="text-align:right;"><h6>Customer GST</h6><p><b>' + r.GSTNO + '</b></p><p>Type: ' + r.gsttype + '</p></div>' +
        '</div>' +
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
            '<div class="sgn"><p style="font-weight:700;color:#1a2942;">for DELVIN DIAMOND TOOL INDUSTRIES</p>' +
            '<div class="sline">Proprietor / Manager</div></div>' +
        '</div>' +
    '</div>';
}

function downloadMonthlyPDF() {
    var pageTitle = '<?php echo ($sel_type === "purchase") ? "PURCHASE INVOICE" : "TAX INVOICE"; ?>';
    var css = '<style>' +
        '.inv-pg { width:750px; background:#fff; border:2px solid #1a2942; margin:0 auto; page-break-after:always; font-family:Segoe UI,Arial,sans-serif; color:#1e293b; font-size:13px; }' +
        '.inv-pg:last-child { page-break-after: auto; }' +
        '.hdr { background:#1a2942; color:#fff; padding:20px 30px; display:flex; justify-content:space-between; align-items:center; }' +
        '.hdr .br p { font-size:11px; opacity:0.8; margin:0; }' +
        '.hdr .br h2 { margin:0; }' +
        '.inv-tp { background:#e8a838; color:#1a2942; font-weight:800; padding:8px 20px; border-radius:4px; font-size:14px; letter-spacing:1px; white-space:nowrap; }' +
        '.gbar { background:#f8fafc; padding:10px 30px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; font-size:12px; }' +
        '.gbar span { color:#64748b; } .gbar b { color:#1a2942; }' +
        '.iinfo { padding:20px 30px; display:flex; justify-content:space-between; border-bottom:1px solid #e2e8f0; }' +
        '.iinfo .blk h6 { font-size:10px; text-transform:uppercase; letter-spacing:1px; color:#64748b; margin:0 0 6px; }' +
        '.iinfo .blk p { font-size:13px; margin:0 0 2px; } .iinfo .blk b { color:#1a2942; }' +
        '.sbox-wrap { padding:20px 30px; display:flex; justify-content:flex-end; }' +
        '.sbox { width:300px; border:1px solid #e2e8f0; border-radius:6px; overflow:hidden; }' +
        '.sr { display:flex; justify-content:space-between; padding:8px 16px; font-size:13px; border-bottom:1px solid #f1f5f9; }' +
        '.sr.stotal { background:#1a2942; color:#fff; font-weight:800; font-size:16px; border:none; padding:12px 16px; }' +
        '.wbank { padding:10px 30px; border-top:1px solid #e2e8f0; font-size:12px; }' +
        '.wds { font-weight:700; margin:0 0 8px; } .bnk { color:#64748b; margin:0; }' +
        '.ftr { padding:15px 30px; border-top:2px solid #1a2942; display:flex; justify-content:space-between; align-items:flex-end; font-size:11px; color:#64748b; }' +
        '.sgn { text-align:right; }' +
        '.sline { border-top:1px solid #1a2942; padding-top:4px; margin-top:30px; width:200px; display:inline-block; }' +
    '</style>';

    var html = css;
    for (var i = 0; i < invoiceData.length; i++) {
        html += buildInvoiceHTML(invoiceData[i], pageTitle);
    }

    var container = document.createElement('div');
    container.innerHTML = html;
    document.body.appendChild(container);

    var opt = {
        margin: 10,
        filename: '<?php echo ucfirst($sel_type); ?>_<?php echo $month_names[$sel_month]; ?>_<?php echo $sel_year; ?>.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true },
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
