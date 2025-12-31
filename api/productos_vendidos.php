<?php
include 'conexion.php';

$desde = $_GET['desde'];
$hasta = $_GET['hasta'];

$sql = "SELECT pr.nombre, SUM(d.cantidad) AS cantidad, SUM(d.cantidad * pr.precio) AS total
        FROM pedido_detalles d
        JOIN productos pr ON pr.id = d.producto_id
        JOIN pedidos p ON p.id = d.pedido_id
        WHERE DATE(p.fecha) BETWEEN '$desde' AND '$hasta'
        GROUP BY pr.id
        ORDER BY pr.nombre ASC";

$res = $conn->query($sql);
$datos = [];

while ($row = $res->fetch_assoc()) {
  $row['cantidad'] = floatval($row['cantidad']);
  $row['total'] = floatval($row['total']);
  $datos[] = $row;
}

header('Content-Type: application/json');
echo json_encode($datos);
?>
