<?php
// api/ventas.php
// Recibe POST { pedido_id, pagos: [{metodo, monto, moneda?}, ...] }
// Guarda pagos con monto_usd y tasa_usada, recalcula total_pagado_usd y actualiza estado del pedido.

require 'conexion.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Método no permitido. Use POST.']);
  exit;
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true) ?? [];

$pedido_id = intval($input['pedido_id'] ?? 0);
$pagos = $input['pagos'] ?? [];

if ($pedido_id <= 0 || !is_array($pagos) || count($pagos) === 0) {
  http_response_code(400);
  echo json_encode(['error' => 'Datos inválidos']);
  exit;
}

// obtener tasa actual (última fila tipo_cambio)
$tasa_bcv = 1.0;
$rs = $conn->query("SELECT tasa FROM tipo_cambio ORDER BY fecha DESC, id DESC LIMIT 1");
if ($rs && $r = $rs->fetch_assoc()) {
  $tasa_bcv = (float)$r['tasa'];
}
if ($tasa_bcv <= 0) $tasa_bcv = 1.0;

$conn->begin_transaction();

try {
  // bloquear pedido para evitar race conditions
  $stmt = $conn->prepare("SELECT id, estado FROM pedidos WHERE id = ? FOR UPDATE");
  if (!$stmt) throw new Exception('Error preparando SELECT pedido: '.$conn->error);
  $stmt->bind_param('i', $pedido_id);
  $stmt->execute();
  $res = $stmt->get_result();
  $row = $res->fetch_assoc();
  if (!$row) throw new Exception('Pedido no encontrado', 404);

  // calcular total_pedido_usd (usar precios desde productos asumiendo precio en USD)
  $sqlTotalPedido = "
    SELECT COALESCE(SUM(CAST(pr.precio AS DECIMAL(12,2)) * CAST(d.cantidad AS DECIMAL(12,3))),0) AS total_pedido_usd
    FROM pedido_detalles d
    JOIN productos pr ON pr.id = d.producto_id
    WHERE d.pedido_id = ?
  ";
  $stmtTot = $conn->prepare($sqlTotalPedido);
  if (!$stmtTot) throw new Exception('Error preparando total pedido: '.$conn->error);
  $stmtTot->bind_param('i', $pedido_id);
  $stmtTot->execute();
  $totalPedidoUsd = (float)($stmtTot->get_result()->fetch_assoc()['total_pedido_usd'] ?? 0.0);

  // total pagado en USD antes de insertar
  $sqlTotalPagadoUsd = "SELECT COALESCE(SUM(monto_usd),0) AS total_pagado_usd FROM pagos WHERE pedido_id = ?";
  $stmtPaid = $conn->prepare($sqlTotalPagadoUsd);
  if (!$stmtPaid) throw new Exception('Error preparando total pagado: '.$conn->error);
  $stmtPaid->bind_param('i', $pedido_id);
  $stmtPaid->execute();
  $totalPagadoAntes = (float)($stmtPaid->get_result()->fetch_assoc()['total_pagado_usd'] ?? 0.0);

  // validar montos entrantes y calcular suma entrante en USD
  $sumaEntranteUsd = 0.0;
  foreach ($pagos as $p) {
    $rawMonto = round((float)($p['monto'] ?? 0), 2);
    if ($rawMonto <= 0) continue;
    $metodo = $p['metodo'] ?? 'efectivo';
    $moneda = $p['moneda'] ?? (in_array($metodo, ['pagomovil','efectivo'], true) ? 'bs' : 'usd');
    $monto_usd = ($moneda === 'bs') ? round($rawMonto / $tasa_bcv, 2) : round($rawMonto, 2);
    $sumaEntranteUsd += $monto_usd;
  }

  if ($sumaEntranteUsd <= 0) {
    $conn->rollback();
    http_response_code(400);
    echo json_encode(['error' => 'No hay montos válidos', 'total_pedido_usd' => round($totalPedidoUsd,2), 'total_pagado_usd' => round($totalPagadoAntes,2)]);
    exit;
  }

  // insertar pagos: guardar moneda, monto_usd, tasa_usada y fecha (usar columna fecha si es la tuya)
  $insertStmt = $conn->prepare("
    INSERT INTO pagos (pedido_id, metodo, monto, moneda, monto_usd, tasa_usada, fecha)
    VALUES (?, ?, ?, ?, ?, ?, NOW())
  ");
  if (!$insertStmt) throw new Exception('Error preparando insert: '.$conn->error);

  foreach ($pagos as $p) {
    $rawMonto = round((float)($p['monto'] ?? 0), 2);
    if ($rawMonto <= 0) continue;
    $metodo = $p['metodo'] ?? 'efectivo';
    $moneda = $p['moneda'] ?? (in_array($metodo, ['pagomovil','efectivo'], true) ? 'bs' : 'usd');
    $monto_usd = ($moneda === 'bs') ? round($rawMonto / $tasa_bcv, 2) : round($rawMonto, 2);
    $tasa_guardar = ($moneda === 'bs') ? $tasa_bcv : 1.0;

    // bind: i = int, s = string, d = double
    $insertStmt->bind_param('isdsdd', $pedido_id, $metodo, $rawMonto, $moneda, $monto_usd, $tasa_guardar);
    if (!$insertStmt->execute()) throw new Exception('Error insert pago: '.$insertStmt->error);
  }

  // recalcular total pagado en USD después de insert
  $stmtPaid2 = $conn->prepare($sqlTotalPagadoUsd);
  if (!$stmtPaid2) throw new Exception('Error preparando total pagado 2: '.$conn->error);
  $stmtPaid2->bind_param('i', $pedido_id);
  $stmtPaid2->execute();
  $totalPagadoFinal = (float)($stmtPaid2->get_result()->fetch_assoc()['total_pagado_usd'] ?? 0.0);

  // comparación segura con tolerancia
  $tp = round($totalPedidoUsd, 2);
  $tpag = round($totalPagadoFinal, 2);
  $epsilon = 0.01;
  $is_pagado_calc = (($tpag + $epsilon) >= $tp) ? 1 : 0;
  $newEstado = $is_pagado_calc ? 'pagado' : 'deudor';

  // actualizar estado en pedidos (si cambió)
  $upd = $conn->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
  if (!$upd) throw new Exception('Error preparando update estado: '.$conn->error);
  $upd->bind_param('si', $newEstado, $pedido_id);
  if (!$upd->execute()) throw new Exception('Error al actualizar estado: '.$upd->error);

  $conn->commit();

  http_response_code(200);
  echo json_encode([
    'ok' => true,
    'mensaje' => 'Pago registrado',
    'estado' => $newEstado,
    'is_pagado_calc' => $is_pagado_calc,
    'total_pedido_usd' => round($totalPedidoUsd,2),
    'total_pagado_usd' => round($totalPagadoFinal,2),
    'tasa_bcv' => $tasa_bcv
  ]);
  exit;

} catch (Exception $e) {
  $conn->rollback();
  $code = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500;
  http_response_code($code);
  echo json_encode(['error' => 'Error al registrar pago: '.$e->getMessage()]);
  exit;
}
