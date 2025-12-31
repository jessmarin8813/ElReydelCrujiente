<?php
// api/listar_pedidos.php
include 'conexion.php';

$estado = $_GET['estado'] ?? null;
$desde = $_GET['desde'] ?? null;
$hasta = $_GET['hasta'] ?? null;

$EPS = 0.01; // tolerancia en USD

$sql = "SELECT p.id,
               p.estado,
               p.mesa_id,
               p.nombre,
               m.nombre AS mesa,
               (SELECT COALESCE(SUM(CAST(pr.precio AS DECIMAL(12,2)) * CAST(d.cantidad AS DECIMAL(12,3))),0)
                FROM pedido_detalles d
                JOIN productos pr ON pr.id = d.producto_id
                WHERE d.pedido_id = p.id) AS total_pedido_usd,
               (SELECT COALESCE(SUM(pa.monto_usd),0) FROM pagos pa WHERE pa.pedido_id = p.id) AS total_pagado_usd
        FROM pedidos p
        LEFT JOIN mesas m ON m.id = p.mesa_id
        WHERE 1=1";

if ($estado) {
  $estadoEsc = $conn->real_escape_string($estado);
  $sql .= " AND p.estado = '{$estadoEsc}'";
}
if ($desde && $hasta) {
  $desdeEsc = $conn->real_escape_string($desde);
  $hastaEsc = $conn->real_escape_string($hasta);
  $sql .= " AND DATE(p.fecha) BETWEEN '{$desdeEsc}' AND '{$hastaEsc}'";
}

$sql .= " ORDER BY p.id DESC";

$res = $conn->query($sql);
$datos = [];
$tasa_bcv = null;

// intentar obtener tasa actual (opcional, útil para mostrar en UI)
$rts = $conn->query("SELECT tasa FROM tipo_cambio ORDER BY fecha DESC, id DESC LIMIT 1");
if ($rts && $rt = $rts->fetch_assoc()) {
  $tasa_bcv = (float)$rt['tasa'];
}

while ($row = $res->fetch_assoc()) {
  $totalPedido = isset($row['total_pedido_usd']) ? (float)$row['total_pedido_usd'] : 0.0;
  $totalPagado = isset($row['total_pagado_usd']) ? (float)$row['total_pagado_usd'] : 0.0;

  $isPagadoCalc = (($totalPagado + $EPS) >= $totalPedido) ? 1 : 0;

  $datos[] = [
    'id' => (int)$row['id'],
    'estado' => $row['estado'],
    'mesa_id' => $row['mesa_id'],
    'nombre' => $row['nombre'],
    'mesa' => $row['mesa'],
    'total_pedido_usd' => round($totalPedido, 2),
    'total_pagado_usd' => round($totalPagado, 2),
    'tasa_bcv' => $tasa_bcv !== null ? round($tasa_bcv,2) : null,
    'is_pagado_calc' => $isPagadoCalc
  ];
}

header('Content-Type: application/json');
echo json_encode($datos);
