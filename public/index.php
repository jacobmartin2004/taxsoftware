<?php
require_once '../src/conn.php';

// Company count
$company_count = 0;
$res = $conn->query("SELECT COUNT(*) as cnt FROM companydata");
if ($res) { $row = $res->fetch_assoc(); $company_count = $row['cnt']; }

// Tool count
$tool_count = 0;
$res = $conn->query("SELECT COUNT(*) as cnt FROM tools");
if ($res) { $row = $res->fetch_assoc(); $tool_count = $row['cnt']; }

// Sales this month
$month = date('m');
$year = date('Y');
$sales_count = 0;
$sales_total = 0;
$res = $conn->query("SELECT COUNT(*) as cnt, IFNULL(SUM(Total),0) as total FROM delvin WHERE SUBSTRING(date,4,2)='$month' AND SUBSTRING(date,7,4)='$year'");
if ($res) { $row = $res->fetch_assoc(); $sales_count = $row['cnt']; $sales_total = $row['total']; }

// Purchase this month
$purchase_count = 0;
$purchase_total = 0;
$res = $conn->query("SELECT COUNT(*) as cnt, IFNULL(SUM(Total),0) as total FROM purchase WHERE SUBSTRING(date,4,2)='$month' AND SUBSTRING(date,7,4)='$year'");
if ($res) { $row = $res->fetch_assoc(); $purchase_count = $row['cnt']; $purchase_total = $row['total']; }

// Recent 5 sales
$recent_sales = [];
$res = $conn->query("SELECT cname, bill, Total, date FROM delvin ORDER BY sno DESC LIMIT 5");
if ($res) { while ($row = $res->fetch_assoc()) $recent_sales[] = $row; }

// Recent 5 purchases
$recent_purchases = [];
$res = $conn->query("SELECT cname, bill, Total, date FROM purchase ORDER BY sno DESC LIMIT 5");
if ($res) { while ($row = $res->fetch_assoc()) $recent_purchases[] = $row; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Delvin Diamond Tool Industries</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1a2942;
            --primary-light: #2c3e5a;
            --accent: #e8a838;
            --accent-hover: #d4952e;
            --success: #22c55e;
            --info: #3b82f6;
            --danger: #ef4444;
            --warning: #f59e0b;
            --bg: #f1f5f9;
            --card-bg: #ffffff;
            --text: #1e293b;
            --text-muted: #64748b;
        }
        * { box-sizing: border-box; }
        body {
            background-color: var(--bg);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: var(--text);
            margin: 0;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: 250px;
            height: 100vh;
            background: var(--primary);
            color: #fff;
            padding: 0;
            z-index: 1000;
            overflow-y: auto;
            transition: transform 0.3s;
        }
        .sidebar-brand {
            padding: 24px 20px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }
        .sidebar-brand h5 {
            font-weight: 700;
            font-size: 15px;
            margin: 0;
            letter-spacing: 0.5px;
            color: var(--accent);
        }
        .sidebar-brand small {
            font-size: 11px;
            color: rgba(255,255,255,0.5);
        }
        .sidebar-nav {
            list-style: none;
            padding: 12px 0;
            margin: 0;
        }
        .sidebar-nav .nav-header {
            padding: 12px 20px 6px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255,255,255,0.35);
        }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 20px;
            color: rgba(255,255,255,0.75);
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background: rgba(255,255,255,0.08);
            color: #fff;
            border-left-color: var(--accent);
        }
        .sidebar-nav a i { font-size: 18px; width: 22px; text-align: center; }

        /* Main Content */
        .main-content {
            margin-left: 250px;
            padding: 0;
            min-height: 100vh;
        }

        /* Top Bar */
        .topbar {
            background: var(--card-bg);
            border-bottom: 1px solid #e2e8f0;
            padding: 14px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .topbar h4 { margin: 0; font-weight: 700; font-size: 20px; }
        .topbar .breadcrumb { margin: 0; font-size: 13px; }

        /* Content Area */
        .content-area { padding: 24px 30px; }

        /* Stat Cards */
        .stat-card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 24px;
            border: 1px solid #e2e8f0;
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
            overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }
        .stat-card .stat-icon {
            width: 48px; height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-bottom: 16px;
        }
        .stat-card .stat-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .stat-card .stat-value {
            font-size: 28px;
            font-weight: 800;
            line-height: 1.2;
        }
        .stat-card .stat-sub {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 4px;
        }
        .icon-blue { background: rgba(59,130,246,0.1); color: var(--info); }
        .icon-green { background: rgba(34,197,94,0.1); color: var(--success); }
        .icon-amber { background: rgba(245,158,11,0.1); color: var(--warning); }
        .icon-red { background: rgba(239,68,68,0.1); color: var(--danger); }

        /* Quick Action Cards */
        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
        }
        .action-card {
            background: var(--card-bg);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            text-decoration: none;
            color: var(--text);
            text-align: center;
            transition: all 0.2s;
        }
        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            color: var(--text);
            border-color: var(--accent);
        }
        .action-card .action-icon {
            width: 56px; height: 56px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 12px;
        }
        .action-card h6 { font-weight: 700; font-size: 14px; margin-bottom: 4px; }
        .action-card p { font-size: 12px; color: var(--text-muted); margin: 0; }

        /* Section Headers */
        .section-header {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--accent);
            display: inline-block;
        }

        /* Table Styling */
        .table-card {
            background: var(--card-bg);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
        }
        .table-card .table { margin: 0; }
        .table-card .table thead th {
            background: var(--primary);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 16px;
            border: none;
        }
        .table-card .table tbody td {
            padding: 12px 16px;
            font-size: 14px;
            vertical-align: middle;
            border-color: #f1f5f9;
        }
        .table-card .table tbody tr:hover { background: #f8fafc; }
        .empty-msg {
            padding: 30px;
            text-align: center;
            color: var(--text-muted);
            font-size: 14px;
        }

        /* Mobile */
        .sidebar-toggle { display: none; }
        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .sidebar-toggle {
                display: inline-flex;
                background: none; border: none;
                font-size: 22px; cursor: pointer;
                color: var(--text); margin-right: 12px;
            }
            .sidebar-overlay {
                display: none;
                position: fixed; inset: 0;
                background: rgba(0,0,0,0.4);
                z-index: 999;
            }
            .sidebar-overlay.show { display: block; }
        }
    </style>
</head>
<body>

<!-- Sidebar Overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- Sidebar -->
<nav class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <h5>DELVIN DIAMOND<br>TOOL INDUSTRIES</h5>
        <small>GST: 33AAAPY1027F1Z3</small>
    </div>
    <ul class="sidebar-nav">
        <li class="nav-header">Main</li>
        <li><a href="index.php" class="active"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a></li>

        <li class="nav-header">Create</li>
        <li><a href="create_invoice.php"><i class="bi bi-receipt"></i> Sales Invoice</a></li>
        <li><a href="create_purchase.php"><i class="bi bi-cart-plus"></i> Purchase Entry</a></li>
        <li><a href="proforma_invoice.php"><i class="bi bi-file-earmark-text"></i> Proforma Invoice</a></li>
        <li><a href="quotation.php"><i class="bi bi-file-earmark-ruled"></i> Quotation</a></li>

        <li class="nav-header">Records</li>
        <li><a href="view_invoices.php"><i class="bi bi-journal-bookmark-fill"></i> View Invoices</a></li>
        <li><a href="../src/view.php"><i class="bi bi-journal-text"></i> Sales Records</a></li>
        <li><a href="view1.php"><i class="bi bi-journal-arrow-down"></i> Purchase Records</a></li>
        <li><a href="../src/printsales.php"><i class="bi bi-printer"></i> Print Sales Report</a></li>
        <li><a href="../src/printpurchase.php"><i class="bi bi-printer"></i> Print Purchase Report</a></li>

        <li class="nav-header">Manage</li>
        <li><a href="../src/companydata.php"><i class="bi bi-building"></i> Companies</a></li>
        <li><a href="add_tool.php"><i class="bi bi-tools"></i> Tools</a></li>
    </ul>
</nav>

<!-- Main Content -->
<div class="main-content">
    <!-- Top Bar -->
    <div class="topbar">
        <div class="d-flex align-items-center">
            <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
            <h4>Dashboard</h4>
        </div>
        <span class="text-muted" style="font-size:13px;">
            <i class="bi bi-calendar3 me-1"></i><?php echo date('d M Y'); ?>
        </span>
    </div>

    <div class="content-area">

        <!-- Stat Cards Row -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon icon-blue"><i class="bi bi-building"></i></div>
                    <div class="stat-label">Companies</div>
                    <div class="stat-value"><?php echo $company_count; ?></div>
                    <div class="stat-sub">Registered clients</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon icon-amber"><i class="bi bi-tools"></i></div>
                    <div class="stat-label">Tools</div>
                    <div class="stat-value"><?php echo $tool_count; ?></div>
                    <div class="stat-sub">Products in catalog</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon icon-green"><i class="bi bi-graph-up-arrow"></i></div>
                    <div class="stat-label">Sales (<?php echo date('M Y'); ?>)</div>
                    <div class="stat-value"><?php echo $sales_count; ?></div>
                    <div class="stat-sub">&#8377; <?php echo number_format($sales_total, 2); ?></div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon icon-red"><i class="bi bi-graph-down-arrow"></i></div>
                    <div class="stat-label">Purchases (<?php echo date('M Y'); ?>)</div>
                    <div class="stat-value"><?php echo $purchase_count; ?></div>
                    <div class="stat-sub">&#8377; <?php echo number_format($purchase_total, 2); ?></div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <h5 class="section-header">Quick Actions</h5>
        <div class="action-grid mb-4">
            <a href="create_invoice.php" class="action-card">
                <div class="action-icon icon-green"><i class="bi bi-receipt"></i></div>
                <h6>Sales Invoice</h6>
                <p>Create new sales bill</p>
            </a>
            <a href="create_purchase.php" class="action-card">
                <div class="action-icon icon-blue"><i class="bi bi-cart-plus"></i></div>
                <h6>Purchase Entry</h6>
                <p>Record new purchase</p>
            </a>
            <a href="proforma_invoice.php" class="action-card">
                <div class="action-icon icon-amber"><i class="bi bi-file-earmark-text"></i></div>
                <h6>Proforma Invoice</h6>
                <p>Preview before billing</p>
            </a>
            <a href="quotation.php" class="action-card">
                <div class="action-icon icon-red"><i class="bi bi-file-earmark-ruled"></i></div>
                <h6>Quotation</h6>
                <p>Price estimation</p>
            </a>
            <a href="../src/companydata.php" class="action-card">
                <div class="action-icon icon-blue"><i class="bi bi-building-add"></i></div>
                <h6>Add Company</h6>
                <p>Register new client</p>
            </a>
            <a href="add_tool.php" class="action-card">
                <div class="action-icon icon-amber"><i class="bi bi-plus-circle"></i></div>
                <h6>Add Tool</h6>
                <p>New product to catalog</p>
            </a>
            <a href="view_invoices.php" class="action-card">
                <div class="action-icon icon-blue"><i class="bi bi-journal-bookmark-fill"></i></div>
                <h6>View Invoices</h6>
                <p>Month-wise invoice list</p>
            </a>
        </div>

        <!-- Recent Tables -->
        <div class="row g-4">
            <div class="col-lg-6">
                <h5 class="section-header">Recent Sales</h5>
                <div class="table-card">
                    <?php if (count($recent_sales) > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Company</th>
                                <th>Bill #</th>
                                <th>Total</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_sales as $s): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($s['cname']); ?></td>
                                <td><?php echo htmlspecialchars($s['bill']); ?></td>
                                <td>&#8377; <?php echo number_format($s['Total'], 2); ?></td>
                                <td><?php echo htmlspecialchars($s['date']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty-msg"><i class="bi bi-inbox" style="font-size:28px;display:block;margin-bottom:8px;"></i>No sales records yet</div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6">
                <h5 class="section-header">Recent Purchases</h5>
                <div class="table-card">
                    <?php if (count($recent_purchases) > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Company</th>
                                <th>Bill #</th>
                                <th>Total</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_purchases as $p): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($p['cname']); ?></td>
                                <td><?php echo htmlspecialchars($p['bill']); ?></td>
                                <td>&#8377; <?php echo number_format($p['Total'], 2); ?></td>
                                <td><?php echo htmlspecialchars($p['date']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty-msg"><i class="bi bi-inbox" style="font-size:28px;display:block;margin-bottom:8px;"></i>No purchase records yet</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div><!-- /content-area -->
</div><!-- /main-content -->

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('show');
    document.getElementById('sidebarOverlay').classList.toggle('show');
}
</script>
</body>
</html>