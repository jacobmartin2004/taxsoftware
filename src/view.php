<!DOCTYPE html>
<html>
<head>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    :root { --primary: #1a2942; --accent: #e8a838; }
    body { background: #f1f5f9; font-family: 'Segoe UI', system-ui, sans-serif; }
    .top-nav { background: var(--primary); padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; }
    .top-nav a { color: rgba(255,255,255,0.8); text-decoration: none; font-size: 14px; padding: 6px 14px; border-radius: 6px; transition: all 0.2s; }
    .top-nav a:hover, .top-nav a.active { background: rgba(255,255,255,0.1); color: #fff; }
    .top-nav .brand { color: var(--accent); font-weight: 700; font-size: 15px; }
    table {
      width: 100%;
      border-collapse: collapse;
      margin: 25px 0;
      font-size: 0.9em;
      font-family: sans-serif;
      min-width: 400px;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
    }

    th, td {
      padding: 12px 15px;
    }

    th {
      background-color: #009879;
      color: #ffffff;
      text-align: center;
    }

    tr {
      border-bottom: 1px solid #dddddd;
    }

    tr:nth-of-type(even) {
      background-color: #f3f3f3;
    }

    tr:last-of-type {
      border-bottom: 2px solid #009879;
    }

    tr:hover {
      background-color: #f1f1f1;
    }

    .container {
      margin-top: 20px;
    }

    .totals-box {
      border: 1px solid #000;
      background-color: #D3F3F5;
      padding: 1px;
      margin: 50px;
      text-align: center;
    }
  </style>
</head>
<body>
<nav class="top-nav">
    <span class="brand">DELVIN DIAMOND TOOLS</span>
    <div>
        <a href="../public/index.php"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
        <a href="../public/create_invoice.php"><i class="bi bi-receipt"></i> Invoice</a>
        <a href="../public/create_purchase.php"><i class="bi bi-cart-plus"></i> Purchase</a>
        <a href="../public/view_invoices.php"><i class="bi bi-journal-bookmark-fill"></i> Invoices</a>
        <a href="companydata.php"><i class="bi bi-building"></i> Companies</a>
        <a href="../public/add_tool.php"><i class="bi bi-tools"></i> Tools</a>
    </div>
</nav>
  <div class="container">
    <div class="row">
      <div class="col-xs-12">
        <font size="5" color="#000000">
          <center>Delvin Diamond Tools</center>
          <center>Somarsampettai</center>
          <center>Trichy,102</center>
        </font>
        <hr>
        <?php
        // Include connection file
        include("conn.php");

        // Fetch records from database
        $sql = "SELECT * from delvin";
        $result = $conn->query($sql);

        // Display table headers
        if ($result->num_rows > 0) {
          echo "<center><table><tr><th>S.No</th><th>GST NO</th><th>Customer Name</th><TH>Bill No</th><th>Date</th><th>taxable amount</th><th>CGST</th><th>SGST</th><th>IGST</th><th>Total</th><th>Delete</th></tr></center>";

          // Display data rows
          $s = 1;
          while($row = $result->fetch_assoc()) {
            $bill = $row["bill"];
            echo "<center><tr><td>".$s."</td><td>".$row["GSTNO"]."</td><td>".$row["cname"]."</td><td>".$row["bill"]."</td><td>".$row["date"]."</td><td>".$row["taxamt"]."</td><td>".$row["cgst"]."</td><td>".$row["sgst"]."</td><td>".$row["igst"]."</td><td>".$row["Total"]."</td><td> <a href='delete.php?bill=".$bill."'>Delete</a>"."</td></tr></center>";
            $s++;
          }
          echo "</table>";
        } else {
          echo "0 results<br>";
        }

        // Calculate and display totals
        $sql1 = "SELECT SUM(taxamt) AS total_taxable FROM delvin";
        $result1 = $conn->query($sql1);
        $row1 = $result1->fetch_assoc();
        $total_taxable = $row1['total_taxable'];

        $sql2 = "SELECT SUM(cgst) AS total_cgst, SUM(sgst) AS total_sgst FROM delvin";
        $result2 = $conn->query($sql2);
        $row2 = $result2->fetch_assoc();
        $total_cgst = $row2['total_cgst'];
        $total_sgst = $row2['total_sgst'];

        $sql3 = "SELECT SUM(igst) AS total_igst FROM delvin";
        $result3 = $conn->query($sql3);
        $row3 = $result3->fetch_assoc();
        $total_igst = $row3['total_igst'];

        $total_gst = $total_cgst + $total_sgst + $total_igst;
        ?>
        <div class="totals-box">
          <p>Total CGST: <?php echo $total_cgst; ?></p>
          <p>Total SGST: <?php echo $total_sgst; ?></p>
          <p>Total IGST: <?php echo $total_igst; ?></p>
          <p>Total GST: <?php echo $total_gst; ?></p>
        </div>

        <?php
        // Close connection
        $conn->close();
        ?>
      <br>
      </div>
    </div>
  </div>
  <center>
    <a href="../public/index.php" class="btn btn-outline-primary">Home Page</a><br><br>
    <a href="printsales.php" class="btn btn-outline-primary">Print sales of the Month</a><br><br>
    <form action="empty_table1.php" method="post">
        <button type="submit" class="btn btn-danger">Empty</button>
    </form>
  </center>
</body>
</html>
