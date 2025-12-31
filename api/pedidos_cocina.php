<?php
include 'conexion.php';

$sql = "SELECT p.id, p.fecha AS hora, p.estado_cocina,
               p.nombre, m.nombre AS mesa,
               GROUP_CONCAT(CONCAT(pr.nombre, ' x', d.cantidad) SEPARATOR '<br>') AS productos
        FROM pedidos p
        JOIN pedido_detalles d ON d.pedido_id = p.id
        JOIN productos pr ON pr.id = d.producto_id
        LEFT JOIN mesas m ON m.id = p.mesa_id
        GROUP BY p.id
        ORDER BY p.id DESC";

$res = $conn->query($sql);
$datos = [];

while ($row = $res->fetch_assoc()) {
  $datos[] = $row;
}

echo json_encode($datos);
?>
