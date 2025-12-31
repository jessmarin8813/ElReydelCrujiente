<?php
include 'conexion.php';

$data = json_decode(file_get_contents("php://input"), true);
$tasa = floatval($data['tasa']);

$sql = "INSERT INTO tipo_cambio (tasa) VALUES ($tasa)";
if ($conn->query($sql)) {
  echo "Tasa actualizada correctamente.";
} else {
  echo "Error al actualizar tasa: " . $conn->error;
}
?>
