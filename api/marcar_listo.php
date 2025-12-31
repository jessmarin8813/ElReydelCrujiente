<?php
include 'conexion.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;

if ($id) {
  $stmt = $conn->prepare("UPDATE pedidos SET estado_cocina = 'listo' WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  echo json_encode(['success' => true]);
} else {
  http_response_code(400);
  echo json_encode(['error' => 'ID inválido']);
}
?>
