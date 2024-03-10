<html>
<head>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
<style>
div {
  padding: 20px;
}
body { 
  background-image: url('blue.jpg');
  background-repeat: no-repeat;
  background-attachment: fixed;
  background-position: top;
  background-size:2000px 900px;
}
</style>
<style>
div.ex1 {
  padding-top: 40px;
  border: 1px solid black;
  margin-top:15px;
  margin-bottom: 100px;
  margin-right: 600px;
  margin-left: 600px;
  background-color: #ffffcc;
}
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
}
</style>
</head>
<body>
<style>div.ex2 {
  padding-top: 5px;
  border:1px solid black;
  margin-right: 90px;
  margin-left: 90px;
  background-color: #33ffff;
  </style>
  <style>
</style>
<font size="5" color="#ffffff" >
<center style="background-color:;">Delvin Diamond Tools</center>
<center>Somarsampettai</center>
<center>Trichy,102</center>
</font>
<hr></hr>
</body>
</html>
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
	echo "<center><table><tr><th>S.No</th><th>GST NO</th><th>Customer Name</th><TH>Bill No</th><th>Date</th><th>taxable amount</th><th>CGST</th><th>SGST</th><th>IGST</th><th>Total</th><th>Delete</th></tr></center>";
  while($row = $result->fetch_assoc()) {
    $bill=$row["bill"];
	echo"<center><tr><td>".$s."</td><td>".$row["GSTNO"]."</td><td>".$row["cname"]."</td><td>".$row["bill"]."</td><td>".$row["date"]."</td><td>".$row["taxamt"]."</td><td>".$row["cgst"]."</td><td>".$row["sgst"]."</td><td>".$row["igst"]."</td><td>".$row["Total"]."</td><td> <a href='delete.php ?bill=".$bill." ' >Delete</a>"."</td></tr></center>";
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
		echo "Total CGST: ".$row['sum(cgst)'];
		echo "<br>";
		echo "Total SGST: ".$row['sum(cgst)'];
        echo "<br>";
}
		$sql1= "SELECT sum(igst) from delvin";
$result1=$conn->query($sql1);
while($row=mysqli_fetch_array($result1)){
		echo "Total IGST: ".$row['sum(igst)'];
		echo "<br>";

}
		$sql1= "SELECT sum(igst)+SUM(cgst)+sum(cgst) from delvin";
$result1=$conn->query($sql1);
while($row=mysqli_fetch_array($result1)){
		echo "Total GST: ".$row['sum(igst)+SUM(cgst)+sum(cgst)'];
		echo "<br>";

}



?>
<br>
<a href="index.html" class="btn btn-outline-primary">Home Page</a></button><br><br>
<a href="printsales.php" class="btn btn-outline-primary">print sales of the month</a></button>