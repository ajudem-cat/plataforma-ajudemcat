<?php
header('Content-Type: text/html; charset=UTF-8');
$servername = "";
$username = "";
$password = "";
$dbname = "";


$codi = $_GET['codi'];


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");

$sql = "DELETE FROM serveis WHERE codi = '" . $codi . "'";

if ($conn->query($sql) === TRUE) {
    header("Location: https://ajudem.cat/eliminat-amb-exit/");
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();


?>
