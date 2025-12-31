<?php
include 'conexion.php';
$res = $conn->query("SELECT * FROM mesas");
echo json_encode($res->fetch_all(MYSQLI_ASSOC));
?>

