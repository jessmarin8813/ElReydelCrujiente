<?php
include 'conexion.php';

$data = json_decode(file_get_contents("php://input"), true);
$nombre = $conn->real_escape_string($data['nombre']);
$precio = floatval($data['precio']);

$sql = "INSERT INTO productos (nombre, precio, disponible) VALUES ('$nombre', $precio, 1)";
if ($conn->query($sql)) {
  echo "Producto agregado correctamente.";
} else {
  echo "Error al agregar producto: " . $conn->error;
}
?>
