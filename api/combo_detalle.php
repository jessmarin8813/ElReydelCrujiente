<?php
require 'conexion.php';
header('Content-Type: application/json');

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
  http_response_code(400);
  echo json_encode(["error" => "ID inválido"]);
  exit;
}

// 🔹 Obtener tasa actual
$tasa = 0;
$resTasa = $conn->query("SELECT tasa FROM tipo_cambio ORDER BY id DESC LIMIT 1");
if ($rowTasa = $resTasa->fetch_assoc()) {
  $tasa = floatval($rowTasa['tasa']);
}

// 🔹 Buscar combo
$res = $conn->query("SELECT id, nombre, precio FROM combos WHERE id = $id");
if (!$row = $res->fetch_assoc()) {
  http_response_code(404);
  echo json_encode(["error" => "Combo no encontrado"]);
  exit;
}

$combo = [
  'id' => intval($row['id']),
  'nombre' => $row['nombre'],
  'precio' => floatval($row['precio']),
  'precio_bs' => round(floatval($row['precio']) * $tasa, 2),
  'productos' => []
];

// 🔹 Obtener productos del combo
$resProd = $conn->query("
  SELECT cp.producto_id, p.nombre, cp.cantidad
  FROM combo_productos cp
  JOIN productos p ON p.id = cp.producto_id
  WHERE cp.combo_id = $id
");

while ($p = $resProd->fetch_assoc()) {
  $combo['productos'][] = [
    'producto_id' => intval($p['producto_id']),
    'nombre' => $p['nombre'],
    'cantidad' => floatval($p['cantidad'])
  ];
}

echo json_encode($combo);
