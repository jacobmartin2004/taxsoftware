<?php
include("conn.php");

$sel_month = isset($_GET['month']) ? $_GET['month'] : date('m');
$sel_year = isset($_GET['year']) ? $_GET['year'] : date('Y');

$where = "WHERE SUBSTRING(date,4,2)='" . $conn->real_escape_string($sel_month) . "' AND SUBSTRING(date,7,4)='" . $conn->real_escape_string($sel_year) . "'";

$sql = "SELECT * FROM purchase $where ORDER BY date ASC";
$result = $conn->query($sql);

$sql_totals = "SELECT SUM(taxamt) AS t_taxamt, SUM(cgst) AS t_cgst, SUM(sgst) AS t_sgst, SUM(igst) AS t_igst, SUM(Total) AS t_total FROM purchase $where";
$res_totals = $conn->query($sql_totals);
$totals = $res_totals->fetch_assoc();

// Balance GST
$sql_sales_gst = "SELECT SUM(cgst) AS s_cgst, SUM(sgst) AS s_sgst, SUM(igst) AS s_igst FROM delvin $where";
$res_sales_gst = $conn->query($sql_sales_gst);
$sales_gst = $res_sales_gst->fetch_assoc();

$sales_total_gst = ($sales_gst['s_cgst'] ?? 0) + ($sales_gst['s_sgst'] ?? 0) + ($sales_gst['s_igst'] ?? 0);
$purchase_total_gst = ($totals['t_cgst'] ?? 0) + ($totals['t_sgst'] ?? 0) + ($totals['t_igst'] ?? 0);
$balance_gst = $sales_total_gst - $purchase_total_gst;

$months = ['01'=>'January','02'=>'February','03'=>'March','04'=>'April','05'=>'May','06'=>'June','07'=>'July','08'=>'August','09'=>'September','10'=>'October','11'=>'November','12'=>'December'];
$month_name = $months[$sel_month] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Purchase Report - <?php echo $month_name . ' ' . $sel_year; ?></title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; color: #000; }
    .print-header { text-align: center; margin: 16px 0 8px; }
    .print-header h3 { font-size: 16px; margin-bottom: 2px; }
    .print-header p { font-size: 11px; color: #555; }
    .print-header h4 { font-size: 14px; margin-top: 8px; border-bottom: 2px solid #1a2942; display: inline-block; padding-bottom: 4px; }
    .filter-bar { text-align: center; padding: 10px; background: #f8f8f8; border-bottom: 1px solid #ddd; margin-bottom: 10px; }
    .filter-bar select { padding: 4px 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px; }
    .filter-bar button { padding: 5px 16px; background: #1a2942; color: #fff; border: none; border-radius: 4px; font-size: 13px; cursor: pointer; margin-left: 8px; }
    table { width: 98%; margin: 0 auto; border-collapse: collapse; }
    th { background: #1a2942; color: #fff; padding: 7px 6px; font-size: 11px; text-transform: uppercase; text-align: center; }
    td { padding: 6px; text-align: center; border-bottom: 1px solid #e0e0e0; font-size: 12px; }
    tr:nth-child(even) { background: #f9f9f9; }
    .totals { width: 98%; margin: 12px auto; border-top: 2px solid #1a2942; padding-top: 8px; display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; font-size: 13px; font-weight: 600; }
    .totals div span { color: #1a2942; }
    .balance-section { text-align: center; margin: 16px auto; max-width: 360px; border: 1px solid #f59e0b; background: #fffbeb; border-radius: 6px; padding: 12px 20px; }
    .balance-section h5 { font-size: 13px; font-weight: 700; color: #1a2942; margin-bottom: 8px; }
    .balance-section .row-item { display: flex; justify-content: space-between; font-size: 12px; margin: 3px 0; }
    .balance-section .bal-val { font-size: 16px; font-weight: 800; color: #b45309; margin-top: 8px; padding-top: 6px; border-top: 2px solid #f59e0b; }
    .action-bar { text-align: center; padding: 16px; }
    .action-bar button, .action-bar a {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 8px 20px; border-radius: 6px; font-size: 13px; font-weight: 600;
      text-decoration: none; border: none; cursor: pointer; margin: 0 4px;
    }
    .btn-print { background: #1a2942; color: #fff; }
    .btn-back { background: #fff; color: #1a2942; border: 1px solid #d1d5db; }
    @media print {
      .filter-bar, .action-bar { display: none !important; }
      body { font-size: 11px; }
    }
  </style>
</head>
<body>

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
    <button onclick="applyFilter()">Filter</button>
</div>

<div class="print-header">
    <h3>DELVIN DIAMOND TOOL INDUSTRIES</h3>
    <p>Somarasampettai, Trichy - 620 102</p>
    <h4>PURCHASE REPORT - <?php echo strtoupper($month_name) . ' ' . $sel_year; ?></h4>
</div>

<?php if ($result && $result->num_rows > 0): ?>
<table>
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
<p style="text-align:center; padding:30px; color:#888;">No purchase records for <?php echo $month_name . ' ' . $sel_year; ?></p>
<?php endif; ?>

<div class="totals">
    <div>Taxable: <span><?php echo number_format($totals['t_taxamt'] ?? 0, 2); ?></span></div>
    <div>CGST: <span><?php echo number_format($totals['t_cgst'] ?? 0, 2); ?></span></div>
    <div>SGST: <span><?php echo number_format($totals['t_sgst'] ?? 0, 2); ?></span></div>
    <div>IGST: <span><?php echo number_format($totals['t_igst'] ?? 0, 2); ?></span></div>
    <div>Total GST: <span><?php echo number_format($purchase_total_gst, 2); ?></span></div>
    <div>Grand Total: <span><?php echo number_format($totals['t_total'] ?? 0, 2); ?></span></div>
</div>

<div class="balance-section">
    <h5>BALANCE GST - <?php echo strtoupper($month_name) . ' ' . $sel_year; ?></h5>
    <div class="row-item"><span>Sales GST:</span><span><?php echo number_format($sales_total_gst, 2); ?></span></div>
    <div class="row-item"><span>Purchase GST:</span><span><?php echo number_format($purchase_total_gst, 2); ?></span></div>
    <div class="bal-val">Balance GST: <?php echo number_format($balance_gst, 2); ?></div>
</div>

<div class="action-bar">
    <button class="btn-print" onclick="window.print()">Print</button>
    <a class="btn-back" href="../public/view1.php?month=<?php echo $sel_month; ?>&year=<?php echo $sel_year; ?>">Back to Purchase</a>
</div>

<?php $conn->close(); ?>

<script>
function applyFilter() {
    var m = document.getElementById('filterMonth').value;
    var y = document.getElementById('filterYear').value;
    window.location.href = 'printpurchase.php?month=' + m + '&year=' + y;
}
</script>
</body>
</html>
