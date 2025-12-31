<?php
include 'conexion.php';

$res = $conn->query("SELECT tasa FROM tipo_cambio ORDER BY id DESC LIMIT 1");

if ($res && $res->num_rows > 0) {
  $fila = $res->fetch_assoc();
  echo json_encode(["valor" => $fila['tasa']]);
} else {
  echo json_encode(["valor" => 0]);
}
?>
