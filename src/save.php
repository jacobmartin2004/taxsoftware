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
      padding: 20px;
      background-color: #fff;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .invoice-details {
      margin-bottom: 20px;
    }
    .btn {
      margin-right: 10px;
    }
  </style>
</head>
<body>
  <div class="container">
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
    $total = $amt + $IGST;
    $GST = $_POST["type"];

    if ($GST == "tngst") {
      echo "<div class='invoice-details'>";
      echo "<h3>Invoice Details</h3>";
      echo "<p>Bill No: $bill</p>";
      echo "<p>Name of the Company: $name</p>";
      echo "<p>Date of Invoice: $date</p>";
      echo "<p>Taxable Amount: $amt</p>";
      echo "<p>CGST: $CGST</p>";
      echo "<p>SGST: $SGST</p>";
      echo "</div>";

      // Insert into database
      $sql = "INSERT INTO delvin (GSTNO, cname, bill, taxamt, cgst, sgst, Total, date) VALUES ('$GSTNO', '$name', $bill, $amt, $CGST, $SGST, $total, '$date')";
    } else if ($GST == "igst") {
      echo "<div class='invoice-details'>";
      echo "<h3>Invoice Details</h3>";
      echo "<p>Bill No: $bill</p>";
      echo "<p>Name of the Company: $name</p>";
      echo "<p>Date of Invoice: $date</p>";
      echo "<p>Taxable Amount: $amt</p>";
      echo "<p>IGST: $IGST</p>";
      echo "</div>";

      // Insert into database
      $sql = "INSERT INTO delvin (GSTNO, cname, bill, taxamt, Total, date, igst) VALUES ('$GSTNO', '$name', $bill, $amt, $total, '$date', $IGST)";
    }

    // Execute SQL query
    if ($conn->query($sql) === TRUE) {
      echo "<div class='alert alert-success' role='alert'>New record created successfully</div>";
    } else {
      echo "<div class='alert alert-danger' role='alert'>Error: " . $sql . "<br>" . $conn->error . "</div>";
    }
    ?>
    <br>
    <a href="index.php" class="btn btn-primary">Home Page</a>
    <a href="view.php" class="btn btn-secondary">Records Saved</a>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
