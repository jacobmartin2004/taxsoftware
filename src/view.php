<?php
require_once 'auth.php';
include("conn.php");

$sel_month = isset($_GET['month']) ? $_GET['month'] : date('m');
$sel_year = isset($_GET['year']) ? $_GET['year'] : date('Y');

$where = "WHERE SUBSTRING(date,4,2)='" . $conn->real_escape_string($sel_month) . "' AND SUBSTRING(date,7,4)='" . $conn->real_escape_string($sel_year) . "'";

$sql = "SELECT * FROM delvin $where ORDER BY date ASC";
$result = $conn->query($sql);

$sql_totals = "SELECT SUM(taxamt) AS t_taxamt, SUM(cgst) AS t_cgst, SUM(sgst) AS t_sgst, SUM(igst) AS t_igst, SUM(Total) AS t_total FROM delvin $where";
$res_totals = $conn->query($sql_totals);
$totals = $res_totals->fetch_assoc();

$months = ['01'=>'January','02'=>'February','03'=>'March','04'=>'April','05'=>'May','06'=>'June','07'=>'July','08'=>'August','09'=>'September','10'=>'October','11'=>'November','12'=>'December'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sales Records - Delvin Diamond Tools</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    :root { --primary: #1a2942; --accent: #e8a838; }
    body { background: #f1f5f9; font-family: 'Segoe UI', system-ui, sans-serif; }
    .top-nav { background: var(--primary); padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; position: relative; }
    .top-nav a { color: rgba(255,255,255,0.8); text-decoration: none; font-size: 14px; padding: 6px 14px; border-radius: 6px; transition: all 0.2s; }
    .top-nav a:hover, .top-nav a.active { background: rgba(255,255,255,0.1); color: #fff; }
    .top-nav .brand { color: var(--accent); font-weight: 700; font-size: 15px; }
    .top-nav .nav-links { display: flex; gap: 4px; flex-wrap: wrap; }
    .top-nav .menu-toggle { display: none; background: none; border: none; color: #fff; font-size: 24px; cursor: pointer; }
    .page-card { max-width: 1100px; margin: 24px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); overflow: hidden; }
    .page-header { background: var(--primary); color: #fff; padding: 20px 28px; }
    .page-header h4 { margin: 0; font-weight: 700; }
    .filter-bar { padding: 16px 28px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
    .filter-bar select { padding: 6px 12px; border-radius: 6px; border: 1px solid #d1d5db; font-size: 14px; }
    .filter-bar .btn-filter { background: var(--primary); color: #fff; border: none; padding: 7px 20px; border-radius: 6px; font-size: 14px; cursor: pointer; }
    .filter-bar .btn-filter:hover { background: #2c3e5a; }
    .table-wrap { padding: 0 28px 20px; overflow-x: auto; }
    .sales-table { width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 13px; }
    .sales-table th { background: var(--primary); color: #fff; padding: 10px 12px; text-align: center; font-size: 12px; text-transform: uppercase; }
    .sales-table td { padding: 9px 12px; border-bottom: 1px solid #f1f5f9; text-align: center; }
    .sales-table tr:nth-child(even) { background: #f8fafc; }
    .sales-table tr:hover { background: #eef2ff; }
    .totals-bar { padding: 16px 28px; background: #f0fdf4; border-top: 2px solid var(--primary); display: flex; gap: 32px; flex-wrap: wrap; justify-content: center; font-size: 14px; font-weight: 600; }
    .totals-bar span { color: var(--primary); }
    .btn-mail { background: #6d28d9; color: #fff; }
    .btn-mail:hover { background: #5b21b6; color: #fff; }
    .mail-section { padding: 16px 28px; border-top: 1px solid #e2e8f0; background: #faf5ff; }
    .mail-section h6 { font-weight: 700; color: var(--primary); margin-bottom: 10px; font-size: 14px; }
    .mail-section .mail-row { display: flex; gap: 10px; align-items: center; margin-bottom: 8px; flex-wrap: wrap; }
    .mail-section .mail-row label { font-size: 13px; font-weight: 600; min-width: 30px; }
    .mail-section .mail-row input { flex: 1; padding: 6px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; min-width: 200px; }
    .actions-bottom { text-align: center; padding: 16px 28px; border-top: 1px solid #e2e8f0; display: flex; gap: 12px; justify-content: center; }
    .actions-bottom .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 20px; border-radius: 6px; font-size: 14px; font-weight: 600; text-decoration: none; border: none; cursor: pointer; }
    .btn-pr { background: var(--primary); color: #fff; }
    .btn-pr:hover { background: #2c3e5a; color: #fff; }
    .btn-outline { background: #fff; color: var(--primary); border: 1px solid #d1d5db; }
    .btn-outline:hover { background: #f1f5f9; color: var(--primary); }
    @media (max-width: 768px) {
        .top-nav { flex-wrap: wrap; padding: 10px 16px; }
        .top-nav .menu-toggle { display: block; }
        .top-nav .nav-links { display: none; width: 100%; flex-direction: column; padding-top: 10px; }
        .top-nav .nav-links.show { display: flex; }
        .top-nav .nav-links a { padding: 10px 14px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .page-card { margin: 10px; border-radius: 8px; }
        .page-header { padding: 14px 16px; }
        .filter-bar { padding: 12px 16px; flex-direction: column; }
        .filter-bar select { width: 100%; font-size: 16px; }
        .filter-bar .btn-filter { width: 100%; padding: 10px; }
        .table-wrap { padding: 0 8px 12px; }
        .sales-table { font-size: 11px; min-width: 600px; }
        .sales-table th, .sales-table td { padding: 6px 4px; }
        .totals-bar { padding: 12px 16px; gap: 16px; font-size: 12px; }
        .actions-bottom { flex-direction: column; padding: 12px 16px; }
        .actions-bottom .btn { width: 100%; justify-content: center; }
    }
  </style>
</head>
<body>
<nav class="top-nav">
    <span class="brand">DELVIN DIAMOND TOOLS</span>
    <button class="menu-toggle" onclick="this.nextElementSibling.classList.toggle('show')"><i class="bi bi-list"></i></button>
    <div class="nav-links">
        <a href="../index.php"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
        <a href="../public/create_invoice.php"><i class="bi bi-receipt"></i> Invoice</a>
        <a href="../public/create_purchase.php"><i class="bi bi-cart-plus"></i> Purchase</a>
        <a href="../public/view_invoices.php"><i class="bi bi-graph-up-arrow"></i> Sales Invoices</a>
        <a href="../public/view_purchases.php"><i class="bi bi-cart-check"></i> Purchase Invoices</a>
        <a href="companydata.php"><i class="bi bi-building"></i> Companies</a>
        <a href="../public/add_tool.php"><i class="bi bi-tools"></i> Tools</a>
    </div>
</nav>

<div class="page-card">
    <div class="page-header">
        <h4><i class="bi bi-graph-up-arrow"></i> Sales Records - <?php echo $months[$sel_month] . ' ' . $sel_year; ?></h4>
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
        <table class="sales-table">
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
        <p style="text-align:center; padding:30px; color:#64748b;">No sales records found for <?php echo $months[$sel_month] . ' ' . $sel_year; ?></p>
        <?php endif; ?>
    </div>

    <div class="totals-bar">
        <div>Taxable: <span><?php echo number_format($totals['t_taxamt'] ?? 0, 2); ?></span></div>
        <div>CGST: <span><?php echo number_format($totals['t_cgst'] ?? 0, 2); ?></span></div>
        <div>SGST: <span><?php echo number_format($totals['t_sgst'] ?? 0, 2); ?></span></div>
        <div>IGST: <span><?php echo number_format($totals['t_igst'] ?? 0, 2); ?></span></div>
        <div>Total GST: <span><?php echo number_format(($totals['t_cgst'] ?? 0) + ($totals['t_sgst'] ?? 0) + ($totals['t_igst'] ?? 0), 2); ?></span></div>
        <div>Grand Total: <span><?php echo number_format($totals['t_total'] ?? 0, 2); ?></span></div>
    </div>

    <div class="actions-bottom">
        <a href="printsales.php?month=<?php echo $sel_month; ?>&year=<?php echo $sel_year; ?>" class="btn btn-pr"><i class="bi bi-printer"></i> Print Sales</a>
        <button class="btn btn-mail" onclick="document.getElementById('mailSection').style.display = document.getElementById('mailSection').style.display === 'none' ? 'block' : 'none'"><i class="bi bi-envelope"></i> Send Mail</button>
        <a href="../index.php" class="btn btn-outline"><i class="bi bi-house"></i> Dashboard</a>
    </div>

    <div class="mail-section" id="mailSection" style="display:none;">
        <h6><i class="bi bi-envelope-fill me-1"></i> Send Sales Record via Yahoo Mail</h6>
        <div class="mail-row">
            <label>To:</label>
            <input type="email" id="mailTo" value="sendhilvisaka@gmail.com">
        </div>
        <div class="mail-row">
            <label>CC:</label>
            <input type="email" id="mailCc" value="sjacobmartin@gmail.com">
        </div>
        <div style="text-align:center; margin-top:10px;">
            <button class="btn btn-mail" onclick="sendYahooMail('sales')"><i class="bi bi-send"></i> Open Yahoo Mail</button>
        </div>
    </div>
</div>

<?php $conn->close(); ?>

<script>
function applyFilter() {
    var m = document.getElementById('filterMonth').value;
    var y = document.getElementById('filterYear').value;
    window.location.href = 'view.php?month=' + m + '&year=' + y;
}

function sendYahooMail(type) {
    var to = document.getElementById('mailTo').value;
    var cc = document.getElementById('mailCc').value;
    var month = document.getElementById('filterMonth');
    var year = document.getElementById('filterYear');
    var monthName = month.options[month.selectedIndex].text;
    var yearVal = year.value;
    var subject = 'Sales Record - ' + monthName + ' ' + yearVal + ' - DELVIN DIAMOND TOOL INDUSTRIES';
    var body = 'Dear Sir/Madam,%0A%0APlease find attached the Sales Record for ' + monthName + ' ' + yearVal + '.%0A%0ARegards,%0ADELVIN DIAMOND TOOL INDUSTRIES%0ATrichy - 620 102%0APh: 0431-2607224';
    var url = 'https://compose.mail.yahoo.com/?to=' + encodeURIComponent(to) + '&cc=' + encodeURIComponent(cc) + '&subject=' + encodeURIComponent(subject) + '&body=' + body;
    window.open(url, '_blank');
}
</script>
</body>
</html>
