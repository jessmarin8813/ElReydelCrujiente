<?php
include 'conexion.php';

$data = json_decode(file_get_contents("php://input"), true);
$pedido_id = intval($data['pedido_id']);
$producto_id = intval($data['producto_id']);
$cantidad = number_format((float)$data['cantidad'], 3, '.', '');

// Verificar si ya existe ese producto en el pedido
$res = $conn->query("SELECT cantidad FROM pedido_detalles WHERE pedido_id = $pedido_id AND producto_id = $producto_id");

if ($res->num_rows > 0) {
  $actual = $res->fetch_assoc()['cantidad'];
  $nuevaCantidad = number_format((float)$actual + (float)$cantidad, 3, '.', '');
  $conn->query("UPDATE pedido_detalles SET cantidad = '$nuevaCantidad' WHERE pedido_id = $pedido_id AND producto_id = $producto_id");
} else {
  $conn->query("INSERT INTO pedido_detalles (pedido_id, producto_id, cantidad) VALUES ($pedido_id, $producto_id, '$cantidad')");
}

echo json_encode(["mensaje" => "Producto agregado correctamente"]);
?>
