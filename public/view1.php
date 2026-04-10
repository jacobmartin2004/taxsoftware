<?php
include("../src/conn.php");

$sel_month = isset($_GET['month']) ? $_GET['month'] : date('m');
$sel_year = isset($_GET['year']) ? $_GET['year'] : date('Y');

$where = "WHERE SUBSTRING(date,4,2)='" . $conn->real_escape_string($sel_month) . "' AND SUBSTRING(date,7,4)='" . $conn->real_escape_string($sel_year) . "'";

$sql = "SELECT * FROM purchase $where ORDER BY date ASC";
$result = $conn->query($sql);

$sql_totals = "SELECT SUM(taxamt) AS t_taxamt, SUM(cgst) AS t_cgst, SUM(sgst) AS t_sgst, SUM(igst) AS t_igst, SUM(Total) AS t_total FROM purchase $where";
$res_totals = $conn->query($sql_totals);
$totals = $res_totals->fetch_assoc();

// Balance GST for the selected month
$sql_sales_gst = "SELECT SUM(cgst) AS s_cgst, SUM(sgst) AS s_sgst, SUM(igst) AS s_igst FROM delvin $where";
$res_sales_gst = $conn->query($sql_sales_gst);
$sales_gst = $res_sales_gst->fetch_assoc();

$sales_total_gst = ($sales_gst['s_cgst'] ?? 0) + ($sales_gst['s_sgst'] ?? 0) + ($sales_gst['s_igst'] ?? 0);
$purchase_total_gst = ($totals['t_cgst'] ?? 0) + ($totals['t_sgst'] ?? 0) + ($totals['t_igst'] ?? 0);
$balance_gst = $sales_total_gst - $purchase_total_gst;

$months = ['01'=>'January','02'=>'February','03'=>'March','04'=>'April','05'=>'May','06'=>'June','07'=>'July','08'=>'August','09'=>'September','10'=>'October','11'=>'November','12'=>'December'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Purchase Records - Delvin Diamond Tools</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    :root { --primary: #1a2942; --accent: #e8a838; }
    body { background: #f1f5f9; font-family: 'Segoe UI', system-ui, sans-serif; }
    .top-nav { background: var(--primary); padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; }
    .top-nav a { color: rgba(255,255,255,0.8); text-decoration: none; font-size: 14px; padding: 6px 14px; border-radius: 6px; transition: all 0.2s; }
    .top-nav a:hover, .top-nav a.active { background: rgba(255,255,255,0.1); color: #fff; }
    .top-nav .brand { color: var(--accent); font-weight: 700; font-size: 15px; }
    .page-card { max-width: 1100px; margin: 24px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); overflow: hidden; }
    .page-header { background: var(--primary); color: #fff; padding: 20px 28px; }
    .page-header h4 { margin: 0; font-weight: 700; }
    .filter-bar { padding: 16px 28px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
    .filter-bar select { padding: 6px 12px; border-radius: 6px; border: 1px solid #d1d5db; font-size: 14px; }
    .filter-bar .btn-filter { background: var(--primary); color: #fff; border: none; padding: 7px 20px; border-radius: 6px; font-size: 14px; cursor: pointer; }
    .filter-bar .btn-filter:hover { background: #2c3e5a; }
    .table-wrap { padding: 0 28px 20px; overflow-x: auto; }
    .pur-table { width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 13px; }
    .pur-table th { background: var(--primary); color: #fff; padding: 10px 12px; text-align: center; font-size: 12px; text-transform: uppercase; }
    .pur-table td { padding: 9px 12px; border-bottom: 1px solid #f1f5f9; text-align: center; }
    .pur-table tr:nth-child(even) { background: #f8fafc; }
    .pur-table tr:hover { background: #eef2ff; }
    .totals-bar { padding: 16px 28px; background: #f0fdf4; border-top: 2px solid var(--primary); display: flex; gap: 32px; flex-wrap: wrap; justify-content: center; font-size: 14px; font-weight: 600; }
    .totals-bar span { color: var(--primary); }
    .balance-box { max-width: 400px; margin: 16px auto; background: #fffbeb; border: 1px solid #f59e0b; border-radius: 8px; padding: 16px 24px; text-align: center; }
    .balance-box h5 { color: var(--primary); margin-bottom: 10px; font-weight: 700; }
    .balance-box .row-item { display: flex; justify-content: space-between; font-size: 14px; margin: 4px 0; }
    .balance-box .balance-val { font-size: 18px; font-weight: 800; color: #b45309; margin-top: 8px; padding-top: 8px; border-top: 2px solid #f59e0b; }
    .actions-bottom { text-align: center; padding: 16px 28px; border-top: 1px solid #e2e8f0; display: flex; gap: 12px; justify-content: center; }
    .actions-bottom .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 20px; border-radius: 6px; font-size: 14px; font-weight: 600; text-decoration: none; border: none; cursor: pointer; }
    .btn-pr { background: var(--primary); color: #fff; }
    .btn-pr:hover { background: #2c3e5a; color: #fff; }
    .btn-outline { background: #fff; color: var(--primary); border: 1px solid #d1d5db; }
    .btn-outline:hover { background: #f1f5f9; color: var(--primary); }
  </style>
</head>
<body>
<nav class="top-nav">
    <span class="brand">DELVIN DIAMOND TOOLS</span>
    <div>
        <a href="index.php"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
        <a href="create_invoice.php"><i class="bi bi-receipt"></i> Invoice</a>
        <a href="create_purchase.php"><i class="bi bi-cart-plus"></i> Purchase</a>
        <a href="view_invoices.php"><i class="bi bi-journal-bookmark-fill"></i> Invoices</a>
        <a href="../src/companydata.php"><i class="bi bi-building"></i> Companies</a>
        <a href="add_tool.php"><i class="bi bi-tools"></i> Tools</a>
    </div>
</nav>

<div class="page-card">
    <div class="page-header">
        <h4><i class="bi bi-cart-check"></i> Purchase Records - <?php echo $months[$sel_month] . ' ' . $sel_year; ?></h4>
    </div>

    <div class="filter-bar">
        <label><strong>Month:</strong></label>
        <select id="filterMonth">
            <?php foreach ($months as $k => $v): ?>
            <option value="<?php echo $k; ?>" <?php echo $k === $sel_month ? 'selected' : ''; ?>><?php echo $v; ?></option>
            <?php endforeach; ?>
        </select>
        <label><strong>Year:</strong></label>
        <select id="filterYear">
            <?php for ($y = 2020; $y <= intval(date('Y')) + 1; $y++): ?>
            <option value="<?php echo $y; ?>" <?php echo $y == $sel_year ? 'selected' : ''; ?>><?php echo $y; ?></option>
            <?php endfor; ?>
        </select>
        <button class="btn-filter" onclick="applyFilter()"><i class="bi bi-funnel"></i> Filter</button>
    </div>

    <div class="table-wrap">
        <?php if ($result && $result->num_rows > 0): ?>
        <table class="pur-table">
            <thead>
                <tr>
                    <th>S.No</th><th>GST NO</th><th>Customer Name</th><th>Bill No</th>
                    <th>Date</th><th>Taxable Amt</th><th>CGST</th><th>SGST</th><th>IGST</th><th>Total</th>
                </tr>
            </thead>
            <tbody>
            <?php $s = 1; while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $s++; ?></td>
                    <td><?php echo htmlspecialchars($row['GSTNO']); ?></td>
                    <td style="text-align:left;"><?php echo htmlspecialchars($row['cname']); ?></td>
                    <td><?php echo $row['bill']; ?></td>
                    <td><?php echo htmlspecialchars($row['date']); ?></td>
                    <td><?php echo number_format($row['taxamt'], 2); ?></td>
                    <td><?php echo number_format($row['cgst'], 2); ?></td>
                    <td><?php echo number_format($row['sgst'], 2); ?></td>
                    <td><?php echo number_format($row['igst'], 2); ?></td>
                    <td><strong><?php echo number_format($row['Total'], 2); ?></strong></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="text-align:center; padding:30px; color:#64748b;">No purchase records found for <?php echo $months[$sel_month] . ' ' . $sel_year; ?></p>
        <?php endif; ?>
    </div>

    <div class="totals-bar">
        <div>Taxable: <span><?php echo number_format($totals['t_taxamt'] ?? 0, 2); ?></span></div>
        <div>CGST: <span><?php echo number_format($totals['t_cgst'] ?? 0, 2); ?></span></div>
        <div>SGST: <span><?php echo number_format($totals['t_sgst'] ?? 0, 2); ?></span></div>
        <div>IGST: <span><?php echo number_format($totals['t_igst'] ?? 0, 2); ?></span></div>
        <div>Total GST: <span><?php echo number_format($purchase_total_gst, 2); ?></span></div>
        <div>Grand Total: <span><?php echo number_format($totals['t_total'] ?? 0, 2); ?></span></div>
    </div>

    <div class="balance-box">
        <h5><i class="bi bi-calculator"></i> BALANCE GST - <?php echo $months[$sel_month] . ' ' . $sel_year; ?></h5>
        <div class="row-item"><span>Sales GST:</span><span><?php echo number_format($sales_total_gst, 2); ?></span></div>
        <div class="row-item"><span>Purchase GST:</span><span><?php echo number_format($purchase_total_gst, 2); ?></span></div>
        <div class="balance-val">Balance GST: <?php echo number_format($balance_gst, 2); ?></div>
    </div>

    <div class="actions-bottom">
        <a href="../src/printpurchase.php?month=<?php echo $sel_month; ?>&year=<?php echo $sel_year; ?>" class="btn btn-pr"><i class="bi bi-printer"></i> Print Purchase</a>
        <a href="index.php" class="btn btn-outline"><i class="bi bi-house"></i> Dashboard</a>
    </div>
</div>

<?php $conn->close(); ?>

<script>
function applyFilter() {
    var m = document.getElementById('filterMonth').value;
    var y = document.getElementById('filterYear').value;
    window.location.href = 'view1.php?month=' + m + '&year=' + y;
}
</script>
</body>
</html>
