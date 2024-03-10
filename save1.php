<head>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
<?php
include("conn.php");
$date=$_POST["date"];
$GSTNO=strtoupper($_POST["gst"]);
$bill=$_POST["bill"];
$name=strtoupper($_POST["name"]);
$amt=$_POST["amt"];
$CGST=$amt*0.09;
$SGST=$amt*0.09;
$IGST=$amt*0.18;
$total=$amt+$IGST;
$GST=$_POST["type"];
if($GST=="tngst")
{
echo "Bill No $bill<br>";
echo "name of the company $name<br>";
echo "Date of invoice $date<br>";
echo "taxable amount $amt<br>";
echo "CGST is $CGST<br>";
echo "CGST is $SGST<br>";

//insert
$sql = "INSERT INTO purchase (GSTNO,cname,bill,taxamt,cgst,sgst,Total,date)  values('$GSTNO','$name',$bill,$amt,$CGST,$SGST,$total,'$date')";
if ($conn->query($sql) === TRUE) {
  echo "New record created successfully";
} else {
  echo "Error: " . $sql . "<br>" . $conn->error;
}
}
else if($GST=="igst")
{
	echo "Bill No $bill<br>";
echo "name of the company $name<br>";
echo "Date of invoice $date<br>";
echo "taxable amount $amt<br>";
echo "IGST is $IGST<br>";
//insert
$sql = "INSERT INTO purchase (GSTNO,cname,bill,taxamt,igst,Total,date) values('$GSTNO','$name',$bill,$amt,$IGST,$total,'$date')";
if ($conn->query($sql) === TRUE) {
  echo "New record created successfully";
} else {
  echo "Error: " . $sql . "<br>" . $conn->error;
}
	
}

?><br>
<a href="project.html">Home Page</a><br>
<a href="view1.php">Records saved</a>


