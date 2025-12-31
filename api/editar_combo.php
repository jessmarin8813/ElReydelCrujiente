<?php
require 'conexion.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$id = intval($data['id'] ?? 0);
$nombre = $conn->real_escape_string($data['nombre'] ?? '');
$estado = $conn->real_escape_string($data['estado'] ?? 'activo');
$productos = $data['productos'] ?? [];

if ($id <= 0 || !$nombre || count($productos) === 0) {
  http_response_code(400);
  echo json_encode(["error" => "Datos inválidos"]);
  exit;
}

$conn->begin_transaction();

try {
  // 🔹 Calcular precio total del combo
  $precio_total = 0;
  foreach ($productos as $p) {
    $pid = intval($p['producto_id']);
    $cant = floatval($p['cantidad']);

    // Obtener precio del producto
    $res = $conn->query("SELECT precio FROM productos WHERE id = $pid");
    if ($row = $res->fetch_assoc()) {
      $precio_unitario = floatval($row['precio']);
      $precio_total += $precio_unitario * $cant;
    }
  }

  // 🔹 Actualizar combo
  $conn->query("UPDATE combos SET nombre='$nombre', precio=$precio_total, estado='$estado' WHERE id=$id");

  // 🔹 Actualizar productos del combo
  $conn->query("DELETE FROM combo_productos WHERE combo_id = $id");
  foreach ($productos as $p) {
    $pid = intval($p['producto_id']);
    $cant = floatval($p['cantidad']);
    $conn->query("INSERT INTO combo_productos (combo_id, producto_id, cantidad) VALUES ($id, $pid, $cant)");
  }

  $conn->commit();
  echo json_encode(["mensaje" => "Combo actualizado", "precio_usd" => round($precio_total, 2)]);
} catch (Exception $e) {
  $conn->rollback();
  http_response_code(500);
  echo json_encode(["error" => "Error al actualizar combo"]);
}
