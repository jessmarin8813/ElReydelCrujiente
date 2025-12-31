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

if ($id <= 0) {
  http_response_code(400);
  echo json_encode(["error" => "ID inválido"]);
  exit;
}

// Usar prepared statement para evitar SQL injection
$stmt = $conn->prepare("DELETE FROM combos WHERE id = ?");
if (!$stmt) {
  http_response_code(500);
  echo json_encode(["error" => "Error al preparar consulta: " . $conn->error]);
  exit;
}

$stmt->bind_param('i', $id);

if ($stmt->execute()) {
  if ($stmt->affected_rows > 0) {
    echo json_encode(["mensaje" => "Combo eliminado correctamente"]);
  } else {
    http_response_code(404);
    echo json_encode(["error" => "Combo no encontrado"]);
  }
} else {
  http_response_code(500);
  echo json_encode(["error" => "Error al eliminar combo: " . $stmt->error]);
}

$stmt->close();
?>
