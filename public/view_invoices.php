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

        @media (max-width: 768px) {
            .filter-bar { flex-direction: column; }
            .inv-table-wrap { overflow-x: auto; }
        }
    </style>
</head>
<body>
<nav class="top-nav">
    <span class="brand">DELVIN DIAMOND TOOLS</span>
    <div>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function confirmDelete(bill) {
    if (confirm('Are you sure you want to delete invoice #' + bill + '? This cannot be undone.')) {
        window.location.href = 'view_invoices.php?delete_bill=' + bill + '&delete_type=<?php echo urlencode($sel_type); ?>&month=<?php echo $sel_month; ?>&year=<?php echo $sel_year; ?>';
    }
}
function downloadMonthlyPDF() {
    var el = document.querySelector('.inv-table-wrap');
    var opt = {
        margin: [10, 10, 10, 10],
        filename: '<?php echo ucfirst($sel_type); ?>_<?php echo $month_names[$sel_month]; ?>_<?php echo $sel_year; ?>.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
    };
    html2pdf().set(opt).from(el).save();
}
</script>
</body>
</html>
