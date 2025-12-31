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
$nombre = trim($data['nombre'] ?? '');
$precio = floatval($data['precio'] ?? 0);

// Validaciones
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

// Usar prepared statement para evitar SQL injection
$stmt = $conn->prepare("INSERT INTO productos (nombre, precio, disponible) VALUES (?, ?, 1)");
if (!$stmt) {
  http_response_code(500);
  echo json_encode(["error" => "Error al preparar consulta: " . $conn->error]);
  exit;
}

$stmt->bind_param('sd', $nombre, $precio);

if ($stmt->execute()) {
  $nuevo_id = $conn->insert_id;
  http_response_code(201);
  echo json_encode([
    "mensaje" => "Producto agregado correctamente",
    "id" => $nuevo_id,
    "nombre" => $nombre,
    "precio" => $precio
  ]);
} else {
  http_response_code(500);
  echo json_encode(["error" => "Error al agregar producto: " . $stmt->error]);
}

$stmt->close();
?>
