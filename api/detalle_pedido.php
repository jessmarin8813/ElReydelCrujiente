<?php
include 'conexion.php';

$id = intval($_GET['id']);

$res = $conn->query("
  SELECT pr.id AS producto_id, pr.nombre, pr.precio, d.cantidad
  FROM pedido_detalles d
  JOIN productos pr ON pr.id = d.producto_id
  WHERE d.pedido_id = $id
");

$datos = [];

while ($row = $res->fetch_assoc()) {
  $datos[] = [
    'producto_id' => intval($row['producto_id']),
    'nombre' => $row['nombre'],
    'precio' => round((float)$row['precio'], 2),
    'cantidad' => round((float)$row['cantidad'], 3)
  ];
}

echo json_encode($datos);
?>
