<?php
require 'conexion.php';
header('Content-Type: application/json; charset=utf-8');

// Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Método no permitido. Use POST.']);
  exit;
}

// Obtener y validar datos de entrada
$data = json_decode(file_get_contents("php://input"), true);
$id = intval($data['id'] ?? 0);
$nombre = trim($data['nombre'] ?? '');
$precio = floatval($data['precio'] ?? 0);
$estado = $data['estado'] ?? 'activo';

// Validaciones
if ($id <= 0) {
  http_response_code(400);
  echo json_encode(["error" => "ID de producto inválido"]);
  exit;
}

if (empty($nombre)) {
  http_response_code(400);
  echo json_encode(["error" => "El nombre del producto es requerido"]);
  exit;
}

if ($precio <= 0) {
  http_response_code(400);
  echo json_encode(["error" => "El precio debe ser mayor a 0"]);
  exit;
}

// Convertir estado a disponible (1 o 0)
$disponible = ($estado === 'activo') ? 1 : 0;

// Usar prepared statement para evitar SQL injection
$stmt = $conn->prepare("UPDATE productos SET nombre = ?, precio = ?, disponible = ? WHERE id = ?");
if (!$stmt) {
  http_response_code(500);
  echo json_encode(["error" => "Error al preparar consulta: " . $conn->error]);
  exit;
}

$stmt->bind_param('sdii', $nombre, $precio, $disponible, $id);

if ($stmt->execute()) {
  if ($stmt->affected_rows > 0) {
    echo json_encode([
      "mensaje" => "Producto actualizado correctamente",
      "id" => $id,
      "nombre" => $nombre,
      "precio" => $precio,
      "disponible" => $disponible
    ]);
  } else {
    http_response_code(404);
    echo json_encode(["error" => "Producto no encontrado o sin cambios"]);
  }
} else {
  http_response_code(500);
  echo json_encode(["error" => "Error al actualizar producto: " . $stmt->error]);
}

$stmt->close();
?>
