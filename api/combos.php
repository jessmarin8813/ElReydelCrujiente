<?php
include 'conexion.php';
header('Content-Type: application/json');

// 🔹 Obtener tasa actual
$tasa = 0;
$resTasa = $conn->query("SELECT tasa FROM tipo_cambio ORDER BY id DESC LIMIT 1");
if ($rowTasa = $resTasa->fetch_assoc()) {
  $tasa = floatval($rowTasa['tasa']);
}

$combos = [];
$res = $conn->query("SELECT id, nombre, precio FROM combos WHERE estado = 'activo'");

while ($row = $res->fetch_assoc()) {
  $combo_id = $row['id'];
  $row['precio'] = floatval($row['precio']);
  $row['precio_bs'] = round($row['precio'] * $tasa, 2);

  // 🔹 Obtener productos del combo
  $productos = [];
  $resProd = $conn->query("
    SELECT p.nombre, cp.cantidad
    FROM combo_productos cp
    JOIN productos p ON p.id = cp.producto_id
    WHERE cp.combo_id = $combo_id
  ");
  while ($p = $resProd->fetch_assoc()) {
    $productos[] = $p;
  }

  $row['productos'] = $productos;
  $combos[] = $row;
}

echo json_encode($combos);
?>
