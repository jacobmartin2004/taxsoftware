<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Invoice Form</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <style>
    body {
      background-color: #f8f9fa;
      font-family: Arial, sans-serif;
    }
    .container {
      max-width: 600px;
      margin: 50px auto;
    }
    .invoice {
      background-color: #fff;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      padding: 20px;
      animation: fadeInUp 0.5s ease; /* Animation for fade in effect */
    }
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="invoice">
      <?php
      include("conn.php");
      $date = $_POST["date"];
      $GSTNO = strtoupper($_POST["gst"]);
      $bill = $_POST["bill"];
      $name = strtoupper($_POST["companyname"]); 
      $amt = $_POST["amt"];
      $CGST = $amt * 0.09;
      $SGST = $amt * 0.09;
      $IGST = $amt * 0.18;
      $p25 = $amt * 0.0025;
      $p6 = $amt * 0.06;
      $total = $amt + $IGST;
      $total25 = $amt + $p25;
      $total6 = $amt + $p6 + $p6;
      $GST = $_POST["type"];
      
      if ($GST == "tngst") {
        echo "<h3>Invoice Details</h3>";
        echo "<p>Bill No: $bill</p>";
        echo "<p>Name of the Company: $name</p>";
        echo "<p>Date of Invoice: $date</p>";
        echo "<p>Taxable Amount: $amt</p>";
        echo "<p>CGST: $CGST</p>";
        echo "<p>SGST: $SGST</p>";
        
        //insert
        $sql = "INSERT INTO purchase (GSTNO, cname, bill, taxamt, cgst, sgst, Total, date) VALUES ('$GSTNO', '$name', $bill, $amt, $CGST, $SGST, $total, '$date')";
      } else if ($GST == "igst") {
        echo "<h3>Invoice Details</h3>";
        echo "<p>Bill No: $bill</p>";
        echo "<p>Name of the Company: $name</p>";
        echo "<p>Date of Invoice: $date</p>";
        echo "<p>Taxable Amount: $amt</p>";
        echo "<p>IGST: $IGST</p>";
        $sql = "INSERT INTO purchase (GSTNO, cname, bill, taxamt, igst, Total, date) VALUES ('$GSTNO', '$name', $bill, $amt, $IGST, $total, '$date')";
      }
      else if ($GST == "25p") {
        echo "<h3>Invoice Details</h3>";
        echo "<p>Bill No: $bill</p>";
        echo "<p>Name of the Company: $name</p>";
        echo "<p>Date of Invoice: $date</p>";
        echo "<p>Taxable Amount: $amt</p>";
        echo "<p>IGST: $p25</p>";
      //insert
      $sql = "INSERT INTO purchase (GSTNO, cname, bill, taxamt, igst, Total, date) VALUES ('$GSTNO', '$name', $bill, $amt, $p25, $total25, '$date')";
    }
    else if ($GST == "6p") {
      echo "<h3>Invoice Details</h3>";
      echo "<p>Bill No: $bill</p>";
      echo "<p>Name of the Company: $name</p>";
      echo "<p>Date of Invoice: $date</p>";
      echo "<p>Taxable Amount: $amt</p>";
      echo "<p>CGST: $p6</p>";
      echo "<p>SGST: $p6</p>";
    //insert
    $sql = "INSERT INTO purchase (GSTNO, cname, bill, taxamt, cgst, sgst, Total, date) VALUES ('$GSTNO', '$name', $bill, $amt, $p6, $p6, $total6, '$date')";
  }
      if ($conn->query($sql) === TRUE) {
        echo "<div class='alert alert-success mt-3' role='alert'>New record created successfully</div>";
      } else {
        echo "<div class='alert alert-danger mt-3' role='alert'>Error: " . $sql . "<br>" . $conn->error . "</div>";
      }
      ?>
      <br>
      <a href="index.php" class="btn btn-primary">Home Page</a>
      <a href="view1.php" class="btn btn-secondary">Records Saved</a>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
