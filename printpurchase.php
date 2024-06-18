<!DOCTYPE html>
<html>
<head>
  <style>
    table {
      border-collapse: collapse;
      width: 98%;
      margin: 0 auto;
    }
    th, td {
      border: 1px solid #33ffff;
      padding: 3px;
      text-align: center;
    }
    th {
      background-color: #f2f2f2;
    }
    @media print {
      .no-print {
        display: none;
      }
    }
  </style>
</head>
<body>
  <font size="4" face="Orbitron">
    <center>Delvin Diamond Tools</center>
    <center>Somarsampettai</center>
    <center>Trichy,102</center>
  </font>
  <hr>
  <form>
    <h4>
      <center>Purchase - <?php echo date('F Y'); ?></center>
    </h4>
    <hr>
  </form>

  <?php
  //view
  include("conn.php");
  //print records
  $sql = "SELECT * FROM `purchase` ORDER BY `purchase`.`date` ASC";
  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
    echo "<center><table><tr><th>S.No</th><th>GST NO</th><th>Customer Name</th><TH>Bill No</th><th>Date</th><th>Taxable Amount</th><th>CGST</th><th>SGST</th><th>IGST</th><th>Total</th></tr></center>";
    $s = 1;
    while($row = $result->fetch_assoc()) {
      echo "<tr><td>".$s."</td><td>".$row["GSTNO"]."</td><td>".$row["cname"]."</td><td>".$row["bill"]."</td><td>".$row["date"]."</td><td>".$row["taxamt"]."</td><td>".$row["cgst"]."</td><td>".$row["sgst"]."</td><td>".$row["igst"]."</td><td>".$row["Total"]."</td></tr>";
      $s++;
    }
    echo "</table>";
  } else {
    echo "<center>0 results</center><br>";
  }

  $sql = "SELECT sum(cgst) as sum_cgst from purchase";
  $result = $conn->query($sql);
  while($row = $result->fetch_assoc()) {
    echo "CGST: ".$row['sum_cgst']."<br>";
    echo "SGST: ".$row['sum_cgst']."<br>";
  }

  $sql1 = "SELECT sum(igst) as sum_igst from purchase";
  $result1 = $conn->query($sql1);
  while($row = $result1->fetch_assoc()) {
    echo "IGST: ".$row['sum_igst']."<br>";
  }

  $sql1 = "SELECT sum(igst) + SUM(cgst) + sum(cgst) as total from purchase";
  $result1 = $conn->query($sql1);
  while($row = $result1->fetch_assoc()) {
    echo "Total: ".$row['total']."<br>";
  }

  echo "<br><br><br><br><center>BALANCE GST</center>";
  echo "------------------------------<br>";

  $sql = "SELECT sum(cgst) + sum(igst) + sum(cgst) as gst_sales from delvin";
  $result = $conn->query($sql);
  while($row = $result->fetch_assoc()) {
    echo "GST Sales:  ".$row['gst_sales']."<br>";
  }

  $sql = "SELECT sum(cgst) + sum(igst) + sum(cgst) as gst_purchase from purchase";
  $result = $conn->query($sql);
  while($row = $result->fetch_assoc()) {
    echo "GST Purchase:  ".$row['gst_purchase']."<br>";
  }

  echo "<center>------------------------------------------</center>";

  $sql = "SELECT 
            (SELECT SUM(cgst) + SUM(cgst) + SUM(igst) FROM delvin) 
            - 
            (SELECT SUM(cgst) + SUM(cgst) + SUM(igst) FROM purchase) 
            as result";
  $result = $conn->query($sql);
  while($row = $result->fetch_assoc()) {
    echo "BALANCE GST: ".$row['result']."<br>";
  }
  ?>

  <br><br>
  <div class="no-print">
    <button onclick="window.print()">Print this page</button>
  </div>
</body>
</html>
