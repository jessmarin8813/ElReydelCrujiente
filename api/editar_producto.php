<?php
include 'conexion.php';

$data = json_decode(file_get_contents("php://input"), true);
$id = intval($data['id']);
$nombre = $conn->real_escape_string($data['nombre']);
$precio = floatval($data['precio']);
$estado = $data['estado'] === 'activo' ? 1 : 0; // convierte a 1 o 0

$sql = "UPDATE productos SET nombre='$nombre', precio=$precio, disponible=$estado WHERE id=$id";
if ($conn->query($sql) && $conn->affected_rows > 0) {
  echo json_encode(["mensaje" => "Producto actualizado correctamente"]);
} else {
  echo json_encode(["error" => "No se actualizó ningún producto"]);
}
?>
