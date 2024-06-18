<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Balance GST - Delvin Diamond Tools</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f0f0f0;
    }
    .container {
      margin-top: 20px;
    }
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
    .totals-box {
      border: 1px solid #ddd;
      background-color: #f9f9f9;
      padding: 15px;
      margin-top: 20px;
      text-align: center;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>
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
        $sql = "SELECT * FROM `purchase` ORDER BY `purchase`.`date` ASC";
        $result = $conn->query($sql);

        // Display table headers
        if ($result->num_rows > 0) {
          echo "<center><table><tr><th>S.No</th><th>GST NO</th><th>Customer Name</th><TH>Bill No</th><th>Date</th><th>taxable amount</th><th>CGST</th><th>SGST</th><th>IGST</th><th>Total</th><th>Delete</th></tr></center>";

          // Display data rows
          $s = 1;
          while($row = $result->fetch_assoc()) {
            $bill = $row["bill"];
            echo "<center><tr><td>".$s."</td><td>".$row["GSTNO"]."</td><td>".$row["cname"]."</td><td>".$row["bill"]."</td><td>".$row["date"]."</td><td>".$row["taxamt"]."</td><td>".$row["cgst"]."</td><td>".$row["sgst"]."</td><td>".$row["igst"]."</td><td>".$row["Total"]."</td><td> <a href='delete1.php?bill=".$bill."'>Delete</a>"."</td></tr></center>";
            $s++;
          }
          echo "</table>";
        } else {
          echo "0 results<br>";
        }

        // Calculate and display totals
        $sql_cgst = "SELECT SUM(cgst) AS total_cgst FROM purchase";
        $result_cgst = $conn->query($sql_cgst);
        $row_cgst = $result_cgst->fetch_assoc();
        $total_cgst = $row_cgst['total_cgst'];

        $sql_sgst = "SELECT SUM(sgst) AS total_sgst FROM purchase";
        $result_sgst = $conn->query($sql_sgst);
        $row_sgst = $result_sgst->fetch_assoc();
        $total_sgst = $row_sgst['total_sgst'];

        $sql_igst = "SELECT SUM(igst) AS total_igst FROM purchase";
        $result_igst = $conn->query($sql_igst);
        $row_igst = $result_igst->fetch_assoc();
        $total_igst = $row_igst['total_igst'];

        $total_gst = $total_cgst + $total_sgst + $total_igst;
        ?>
        <div class="totals-box">
          <h6>Total CGST: <?php echo $total_cgst; ?></h6>
          <h6>Total SGST: <?php echo $total_sgst; ?></h6>
          <h6>Total IGST: <?php echo $total_igst; ?></h6>
          <h6>Total GST: <?php echo $total_gst; ?></h6>
        </div>

        <?php
        // Calculate balance GST
        echo "<div class='totals-box'>";
        echo "<h4>BALANCE GST</h5>";
        

        // Total sales
        $sql_sales = "SELECT SUM(cgst) + SUM(igst) + SUM(cgst) AS total_sales FROM delvin";
        $result_sales = $conn->query($sql_sales);
        $row_sales = $result_sales->fetch_assoc();
        $total_sales = $row_sales['total_sales'];
        echo "Total Sales:  " . $total_sales . "<br>";

        // Total purchase
        $sql_purchase = "SELECT SUM(cgst) + SUM(igst) + SUM(cgst) AS total_purchase FROM purchase";
        $result_purchase = $conn->query($sql_purchase);
        $row_purchase = $result_purchase->fetch_assoc();
        $total_purchase = $row_purchase['total_purchase'];
        echo "Total Purchase:  " . $total_purchase . "<br>";

        // Balance GST
        $sql_balance_gst = "SELECT (SELECT (SUM(cgst) + SUM(cgst) + SUM(igst)) FROM delvin) - (SELECT (SUM(cgst) + SUM(cgst) + SUM(igst)) FROM purchase) AS result";
        $result_balance_gst = $conn->query($sql_balance_gst);
        $row_balance_gst = $result_balance_gst->fetch_assoc();
        $balance_gst = $row_balance_gst['result'];
        echo "Total GST: " . $balance_gst . "<br>";

        echo "</div>";

        // Close connection
        $conn->close();
        ?>
      </div>
    </div>
  </div>
  <br>
  <center>
    <a href="index.html" class="btn btn-outline-primary">Home Page</a><br><br>
    <a href="printpurchase.php" class="btn btn-outline-primary">Print Purchase of the Month</a>
  </center>
</body>
</html>
