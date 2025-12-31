<?php
// verificar_pago.php
// Entrada: ?id=NN (pedido id)
// Salida JSON con totales en USD y Bs y is_pagado_calc

require 'conexion.php';
header('Content-Type: application/json; charset=utf-8');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
  http_response_code(400);
  echo json_encode(['error' => 'Pedido id inválido']);
  exit;
}

// obtener tasa actual (última fila tipo_cambio)
$tasa_bcv = 1.0;
$rs = $conn->query("SELECT tasa FROM tipo_cambio ORDER BY fecha DESC, id DESC LIMIT 1");
if ($rs && $r = $rs->fetch_assoc()) {
  $tasa_bcv = (float)$r['tasa'];
}
if ($tasa_bcv <= 0) $tasa_bcv = 1.0;

// total pedido en USD (precios desde productos)
$sqlTotal = "
  SELECT COALESCE(SUM(CAST(pr.precio AS DECIMAL(12,2)) * CAST(d.cantidad AS DECIMAL(12,3))),0) AS total_usd
  FROM pedido_detalles d
  JOIN productos pr ON pr.id = d.producto_id
  WHERE d.pedido_id = ?
";
$stmt = $conn->prepare($sqlTotal);
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$total_usd = (float)($row['total_usd'] ?? 0.0);

// total pagado en USD (sum monto_usd)
$sqlPag = "SELECT COALESCE(SUM(monto_usd),0) AS total_pagado_usd FROM pagos WHERE pedido_id = ?";
$stmt2 = $conn->prepare($sqlPag);
$stmt2->bind_param('i', $id);
$stmt2->execute();
$res2 = $stmt2->get_result();
$row2 = $res2->fetch_assoc();
$total_pagado_usd = (float)($row2['total_pagado_usd'] ?? 0.0);

// calcular versiones en Bs para compatibilidad
$total_bs = round($total_usd * $tasa_bcv, 2);
$total_pagado_bs = round($total_pagado_usd * $tasa_bcv, 2);

// is_pagado_calc con tolerancia
$EPS = 0.01;
$is_pagado = (($total_pagado_usd + $EPS) >= $total_usd) ? 1 : 0;

// información base del pedido (opcional)
$q = $conn->prepare("SELECT id, nombre, mesa_id, estado FROM pedidos WHERE id = ?");
$q->bind_param('i', $id);
$q->execute();
$rq = $q->get_result()->fetch_assoc() ?: [];

// devolver varios nombres para compatibilidad con frontend viejo
echo json_encode([
  'ok' => true,
  'id' => $id,
  'estado' => $rq['estado'] ?? 'desconocido',
  'nombre' => $rq['nombre'] ?? null,
  'mesa' => $rq['mesa_id'] ?? null,
  // nuevos campos (fuente de verdad en USD)
  'total_pedido_usd' => round($total_usd, 2),
  'total_pagado_usd' => round($total_pagado_usd, 2),
  // versiones en Bs para compatibilidad visual
  'total_pedido_bs' => $total_bs,
  'total_pagado_bs' => $total_pagado_bs,
  // legacy keys (si clientes antiguos buscan estos nombres)
  'total_publico' => number_format(round($total_usd,2), 2, '.', ''), // legacy: mantener algo
  'total_pagado' => number_format(round($total_pagado_usd,2), 2, '.', ''),
  'total' => number_format(round($total_usd,2), 2, '.', ''),
  'is_pagado_calc' => $is_pagado,
  'tasa_bcv' => $tasa_bcv
]);
