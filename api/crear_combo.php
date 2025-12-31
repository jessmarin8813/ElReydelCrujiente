<?php
require 'conexion.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$nombre = $conn->real_escape_string($data['nombre'] ?? '');
$productos = $data['productos'] ?? [];

if (!$nombre || count($productos) === 0) {
  http_response_code(400);
  echo json_encode(["error" => "Datos incompletos"]);
  exit;
}

$conn->begin_transaction();

try {
  // 🔹 Insertar combo con precio temporal
  $conn->query("INSERT INTO combos (nombre, precio) VALUES ('$nombre', 0)");
  $combo_id = $conn->insert_id;

  // 🔹 Calcular precio total e insertar productos
  $precio_total = 0;
  foreach ($productos as $p) {
    $pid = intval($p['producto_id']);
    $cant = floatval($p['cantidad']);

    $res = $conn->query("SELECT precio FROM productos WHERE id = $pid");
    if ($row = $res->fetch_assoc()) {
      $precio_unitario = floatval($row['precio']);
      $precio_total += $precio_unitario * $cant;
    }

    $conn->query("INSERT INTO combo_productos (combo_id, producto_id, cantidad) VALUES ($combo_id, $pid, $cant)");
  }

  // 🔹 Actualizar precio final del combo
  $conn->query("UPDATE combos SET precio = $precio_total WHERE id = $combo_id");

  // 🔹 Obtener tasa actual para calcular precio en Bs
  $tasa = 0;
  $resTasa = $conn->query("SELECT tasa FROM tipo_cambio ORDER BY id DESC LIMIT 1");
  if ($rowTasa = $resTasa->fetch_assoc()) {
    $tasa = floatval($rowTasa['tasa']);
  }
  $precio_bs = round($precio_total * $tasa, 2);

  $conn->commit();
  echo json_encode([
    "mensaje" => "Combo creado exitosamente",
    "combo_id" => $combo_id,
    "precio_usd" => round($precio_total, 2),
    "precio_bs" => $precio_bs
  ]);
} catch (Exception $e) {
  $conn->rollback();
  http_response_code(500);
  echo json_encode(["error" => "Error al crear combo"]);
}
