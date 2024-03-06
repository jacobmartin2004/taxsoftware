<head>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
<style>
	table, td, th {  
  border: 1px solid #ddd;
  text-align: left;
}

table {
  border-collapse: collapse;
  width: 100%;
}

th, td {
  padding: 15px;
}</style>
<html>
<font size=4 face="Orbitron">
<center>Delvin Diamond Tools</center>
<center>Somarsampettai</center>
<center>Trichy,102</center></font>
<hr></hr>  <form>
<h4><center>SALES-----<input type=textbox></center></h4>
<hr></hr>  </form>
<?php
//view
include("conn.php");
//print records
$sql = "SELECT * from delvin";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
	$sql = "SELECT * from delvin";
	$result = $conn->query($sql);
	$s=1;
	echo "<center><table border=5 color=#33ffff><tr><th>S.No</th><th>GST NO</th><th>Customer Name</th><TH>Bill No</th><th>Date</th><th>taxable amount</th><th>CGST</th><th>SGST</th><th>IGST</th><th>Total</th></tr></center>";
  while($row = $result->fetch_assoc()) {
    $bill=$row["bill"];
	echo"<center><tr><td>".$s."</td><td>".$row["GSTNO"]."</td><td>".$row["cname"]."</td><td>".$row["bill"]."</td><td>".$row["date"]."</td><td>".$row["taxamt"]."</td><td>".$row["cgst"]."</td><td>".$row["sgst"]."</td><td>".$row["igst"]."</td><td>".$row["Total"]."</td></tr></center>";
	$s++;
  }
  echo "</table>";
} else {
  echo "0 results<br>";
}
$sql1= "SELECT sum(taxamt) from delvin";
$result1=$conn->query($sql1);
while($row=mysqli_fetch_array($result1)){
		echo "Total taxable: ".$row['sum(taxamt)'];
		echo "<br>";
}
$sql= "SELECT sum(cgst) from delvin";
$result=$conn->query($sql);
while($row=mysqli_fetch_array($result)){
		echo "CGST: ".$row['sum(cgst)'];
		echo "<br>";
		echo "SGST: ".$row['sum(cgst)'];
        echo "<br>";
}
		$sql1= "SELECT sum(igst) from delvin";
$result1=$conn->query($sql1);
while($row=mysqli_fetch_array($result1)){
		echo "IGST: ".$row['sum(igst)'];
		echo "<br>";
}
		$sql1= "SELECT sum(igst)+SUM(cgst)+sum(cgst) from delvin";
$result1=$conn->query($sql1);
while($row=mysqli_fetch_array($result1)){
		echo "Total: ".$row['sum(igst)+SUM(cgst)+sum(cgst)'];
		echo "<br>";

}


?>
<BR>
<BR>
<div> <button onclick="window.print()">Print this page</button></div>