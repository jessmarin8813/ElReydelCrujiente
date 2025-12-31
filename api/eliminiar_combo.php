<?php
require 'conexion.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$id = intval($data['id'] ?? 0);

if ($id <= 0) {
  http_response_code(400);
  echo json_encode(["error" => "ID inválido"]);
  exit;
}

$conn->query("DELETE FROM combos WHERE id = $id");
echo json_encode(["mensaje" => "Combo eliminado"]);
