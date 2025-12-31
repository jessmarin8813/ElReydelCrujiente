<?php
include 'conexion.php';

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'];
$estado = $data['estado'];

$conn->query("UPDATE mesas SET estado = '$estado' WHERE id = $id");
?>
