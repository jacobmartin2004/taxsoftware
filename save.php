
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
$sql = "INSERT INTO delvin (GSTNO,cname,bill,taxamt,cgst,sgst,Total,date)  values('$GSTNO','$name',$bill,$amt,$CGST,$SGST,$total,'$date')";
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
$sql = "INSERT INTO delvin (GSTNO,cname,bill,taxamt,Total,date,igst) values('$GSTNO','$name',$bill,$amt,$total,'$date',$IGST)";
if ($conn->query($sql) === TRUE) {
  echo "New record created successfully";
} else {
  echo "Error: " . $sql . "<br>" . $conn->error;
}
	
}

?><br>
<a href="project.html">Home Page</a><br>
<a href="view.php">Records saved</a>


