<?php
if(isset($_GET["bill"]))
{
include("conn.php");
$bill=$_GET["bill"];
$sql = "DELETE FROM purchase WHERE bill=$bill";
$result=$conn->query($sql);
if ($result) {
  header("location:view1.php");
} else
{
  echo "Error deleting record: " . $conn->error;
}
}
?><br>
<a href="purchase.html">Home Page</a><br>
<a href="view1.php">Records saved</a>
