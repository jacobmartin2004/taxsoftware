<?php
if(isset($_GET["bill"]))
{
include("conn.php");
$bill=$_GET["bill"];
$sql = "DELETE FROM delvin WHERE bill=$bill";
$result=$conn->query($sql);
if ($result) {
  header("location:view.php");
} else
{
  echo "Error deleting record: " . $conn->error;
}
}
?><br>
<a href="project.html">Home Page</a><br>
<a href="view.php">Records saved</a>
