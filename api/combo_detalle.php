<?php
require 'conexion.php';
header('Content-Type: application/json; charset=utf-8');

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
  http_response_code(400);
  echo json_encode(["error" => "ID inválido"]);
  exit;
}

// 🔹 Obtener tasa actual
$tasa = 1.0;
$resTasa = $conn->query("SELECT tasa FROM tipo_cambio ORDER BY id DESC LIMIT 1");
if ($resTasa && $rowTasa = $resTasa->fetch_assoc()) {
  $tasa = floatval($rowTasa['tasa']);
}

// 🔹 Buscar combo
$stmt = $conn->prepare("SELECT id, nombre, precio FROM combos WHERE id = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "Error preparando consulta: " . $conn->error]);
    exit;
}
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if (!$row = $res->fetch_assoc()) {
  http_response_code(404);
  echo json_encode(["error" => "Combo no encontrado"]);
  exit;
}
$stmt->close();

$combo = [
  'id' => intval($row['id']),
  'nombre' => $row['nombre'],
  'precio' => floatval($row['precio']),
  'precio_bs' => round(floatval($row['precio']) * $tasa, 2),
  'productos' => []
];

// 🔹 Obtener productos del combo
$stmtProd = $conn->prepare("
  SELECT cp.producto_id, p.nombre, cp.cantidad
  FROM combo_productos cp
  JOIN productos p ON p.id = cp.producto_id
  WHERE cp.combo_id = ?
");
if (!$stmtProd) {
    http_response_code(500);
    echo json_encode(["error" => "Error preparando consulta productos: " . $conn->error]);
    exit;
}

$stmtProd->bind_param("i", $id);
$stmtProd->execute();
$resProd = $stmtProd->get_result();

while ($p = $resProd->fetch_assoc()) {
  $combo['productos'][] = [
    'producto_id' => intval($p['producto_id']),
    'nombre' => $p['nombre'],
    'cantidad' => floatval($p['cantidad'])
  ];
}
$stmtProd->close();

echo json_encode($combo);
?>
