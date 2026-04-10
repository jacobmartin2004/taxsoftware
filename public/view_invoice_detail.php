<?php
require_once '../src/conn.php';

$type = isset($_GET['type']) ? $_GET['type'] : 'sales';
$bill = isset($_GET['bill']) ? intval($_GET['bill']) : 0;

if ($bill <= 0) { header('Location: view_invoices.php'); exit(); }

$table = ($type === 'purchase') ? 'purchase' : 'delvin';
$page_title = ($type === 'purchase') ? 'PURCHASE INVOICE' : 'TAX INVOICE';

$stmt = $conn->prepare("SELECT GSTNO, cname, bill, taxamt, cgst, sgst, igst, Total, date FROM `$table` WHERE bill = ?");
$stmt->bind_param('i', $bill);
$stmt->execute();
$result = $stmt->get_result();
$inv = $result->fetch_assoc();
$stmt->close();

if (!$inv) { echo "<p style='padding:40px;font-family:sans-serif;'>Invoice not found.</p>"; exit(); }

// Get company address
$address = '';
$state = '';
$district = '';
$gst_type_display = '';
$cstmt = $conn->prepare("SELECT address, state, district, gsttype FROM companydata WHERE gstno = ?");
$cstmt->bind_param('s', $inv['GSTNO']);
$cstmt->execute();
$cres = $cstmt->get_result();
if ($crow = $cres->fetch_assoc()) {
    $address = $crow['address'] ?? '';
    $state = $crow['state'] ?? '';
    $district = $crow['district'] ?? '';
    $gst_type_display = strtoupper($crow['gsttype'] ?? '');
}
$cstmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> #<?php echo $inv['bill']; ?></title>
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
        .gst-bar {
            background: #f8fafc; padding: 10px 30px; border-bottom: 1px solid #e2e8f0;
            display: flex; justify-content: space-between; font-size: 12px;
        }
        .gst-bar span { color: #64748b; }
        .gst-bar strong { color: #1a2942; }
        .inv-info {
            padding: 20px 30px; display: flex; justify-content: space-between;
            border-bottom: 1px solid #e2e8f0;
        }
        .inv-info .block h6 { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-bottom: 6px; }
        .inv-info .block p { font-size: 13px; margin-bottom: 2px; }
        .inv-info .block p strong { color: #1a2942; }

        .inv-amounts { padding: 20px 30px; }
        .inv-amounts table { width: 100%; border-collapse: collapse; }
        .inv-amounts thead th {
            background: #1a2942; color: #fff; padding: 10px 12px;
            font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; text-align: left;
        }
        .inv-amounts tbody td { padding: 10px 12px; font-size: 13px; border-bottom: 1px solid #f1f5f9; }
        .inv-amounts thead th:last-child, .inv-amounts tbody td:last-child { text-align: right; }

        .inv-summary { padding: 0 30px 20px; display: flex; justify-content: flex-end; }
        .summary-box { width: 300px; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; }
        .summary-row { display: flex; justify-content: space-between; padding: 8px 16px; font-size: 13px; border-bottom: 1px solid #f1f5f9; }
        .summary-row.total { background: #1a2942; color: #fff; font-weight: 800; font-size: 16px; border: none; padding: 12px 16px; }

        .inv-footer {
            padding: 15px 30px; border-top: 2px solid #1a2942;
            display: flex; justify-content: space-between; align-items: flex-end;
            font-size: 11px; color: #64748b;
        }
        .inv-footer .sign { text-align: right; }
        .inv-footer .sign .line { border-top: 1px solid #1a2942; padding-top: 4px; margin-top: 30px; width: 200px; display: inline-block; }

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

        .top-nav { background: #1a2942; padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; }
        .top-nav a { color: rgba(255,255,255,0.8); text-decoration: none; font-size: 14px; padding: 6px 14px; border-radius: 6px; transition: all 0.2s; }
        .top-nav a:hover, .top-nav a.active { background: rgba(255,255,255,0.1); color: #fff; }
        .top-nav .brand { color: #e8a838; font-weight: 700; font-size: 15px; }

        @media print {
            body { background: #fff; }
            .actions-bar { display: none !important; }
            .invoice-page { border: none; margin: 0; }
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

<div class="invoice-page">
    <div class="inv-header">
        <div class="brand">
            <h2>DELVIN DIAMOND TOOL INDUSTRIES</h2>
            <p>1/56, Easu Street, Somarasampettai (PO), Trichy - 620 102</p>
        </div>
        <div class="inv-type"><?php echo $page_title; ?></div>
    </div>

    <div class="gst-bar">
        <div><span>GSTIN:</span> <strong>33AAAPY1027F1Z3</strong></div>
        <div><span>HSN Code:</span> <strong>68042110</strong></div>
        <div><span>Bill #:</span> <strong><?php echo htmlspecialchars($inv['bill']); ?></strong></div>
        <div><span>Date:</span> <strong><?php echo htmlspecialchars($inv['date']); ?></strong></div>
    </div>

    <div class="inv-info">
        <div class="block">
            <h6>Bill To</h6>
            <p><strong><?php echo htmlspecialchars($inv['cname']); ?></strong></p>
            <p><?php echo htmlspecialchars($address); ?></p>
            <p><?php echo htmlspecialchars($district); ?><?php echo $district && $state ? ', ' : ''; ?><?php echo htmlspecialchars($state); ?></p>
        </div>
        <div class="block" style="text-align:right;">
            <h6>Customer GST</h6>
            <p><strong><?php echo htmlspecialchars($inv['GSTNO']); ?></strong></p>
            <p>Type: <?php echo $gst_type_display; ?></p>
        </div>
    </div>

    <div class="inv-amounts">
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Taxable Amount</th>
                    <th>CGST (9%)</th>
                    <th>SGST (9%)</th>
                    <th>IGST (18%)</th>
                    <th>Total (₹)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Goods / Services</td>
                    <td>₹ <?php echo number_format($inv['taxamt'], 2); ?></td>
                    <td>₹ <?php echo number_format($inv['cgst'], 2); ?></td>
                    <td>₹ <?php echo number_format($inv['sgst'], 2); ?></td>
                    <td>₹ <?php echo number_format($inv['igst'], 2); ?></td>
                    <td>₹ <?php echo number_format($inv['Total'], 2); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="inv-summary">
        <div class="summary-box">
            <div class="summary-row">
                <span>Taxable Amount</span>
                <span>₹ <?php echo number_format($inv['taxamt'], 2); ?></span>
            </div>
            <?php if ($inv['cgst'] > 0 || $inv['sgst'] > 0): ?>
            <div class="summary-row">
                <span>CGST (9%)</span>
                <span>₹ <?php echo number_format($inv['cgst'], 2); ?></span>
            </div>
            <div class="summary-row">
                <span>SGST (9%)</span>
                <span>₹ <?php echo number_format($inv['sgst'], 2); ?></span>
            </div>
            <?php endif; ?>
            <?php if ($inv['igst'] > 0): ?>
            <div class="summary-row">
                <span>IGST (18%)</span>
                <span>₹ <?php echo number_format($inv['igst'], 2); ?></span>
            </div>
            <?php endif; ?>
            <div class="summary-row total">
                <span>TOTAL</span>
                <span>₹ <?php echo number_format($inv['Total'], 2); ?></span>
            </div>
        </div>
    </div>

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

<div class="actions-bar">
    <button class="btn btn-print" onclick="window.print()"><i class="bi bi-printer"></i> Print Invoice</button>
    <button class="btn btn-download" onclick="downloadPDF()"><i class="bi bi-download"></i> Download PDF</button>
    <a href="view_invoices.php?type=<?php echo htmlspecialchars($type); ?>" class="btn btn-back"><i class="bi bi-arrow-left"></i> Back to List</a>
    <a href="index.php" class="btn btn-dash"><i class="bi bi-grid-1x2"></i> Dashboard</a>
</div>

<script>
function downloadPDF() {
    var element = document.querySelector('.invoice-page');
    var opt = {
        margin: 10,
        filename: '<?php echo $page_title; ?>_<?php echo $inv['bill']; ?>_<?php echo htmlspecialchars($inv['cname']); ?>.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };
    html2pdf().set(opt).from(element).save();
}
</script>

</body>
</html>
