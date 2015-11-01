<?php
$db = mysqli_connect("localhost", "USER", "PASS", "DB_Name");
if(!$db)
{
  exit("Verbindungsfehler: ".mysqli_connect_error());
}
?>
