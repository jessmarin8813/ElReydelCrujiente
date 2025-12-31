<?php
include 'conexion.php';

$desde = $_GET['desde'];
$hasta = $_GET['hasta'];

$sql = "SELECT id, fecha FROM pedidos 
        WHERE DATE(fecha) BETWEEN '$desde' AND '$hasta' AND estado = 'pagado'
        ORDER BY id DESC";
$res = $conn->query($sql);
$datos = [];
$totalGlobal = 0;
$metodos = [];

while ($pedido = $res->fetch_assoc()) {
  $id = $pedido['id'];

  $sqlProd = "SELECT pr.nombre, d.cantidad, pr.precio
              FROM pedido_detalles d
              JOIN productos pr ON pr.id = d.producto_id
              WHERE d.pedido_id = $id";
  $resProd = $conn->query($sqlProd);
  $productos = [];
  $totalPedido = 0;
  while ($p = $resProd->fetch_assoc()) {
    $productos[] = $p;
    $totalPedido += $p['precio'] * $p['cantidad'];
  }
  $totalGlobal += $totalPedido;

  $sqlPago = "SELECT metodo, monto FROM pagos WHERE pedido_id = $id";
  $resPago = $conn->query($sqlPago);
  $pagos = [];
  while ($p = $resPago->fetch_assoc()) {
    $pagos[] = $p;
    $metodo = $p['metodo'];
    $metodos[$metodo] = ($metodos[$metodo] ?? 0) + $p['monto'];
  }

  $datos[] = [
    "id" => $id,
    "fecha" => $pedido['fecha'],
    "productos" => $productos,
    "pagos" => $pagos
  ];
}

header('Content-Type: application/json');
echo json_encode([
  "resumen" => [
    "total_ventas" => $totalGlobal,
    "metodos" => $metodos
  ],
  "pedidos" => $datos
]);
?>
