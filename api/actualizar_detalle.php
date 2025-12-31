<?php
include 'conexion.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Método no permitido. Use POST.']);
  exit;
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true) ?? [];

$pedido_id = intval($input['pedido_id'] ?? 0);
$detalles = $input['detalles'] ?? [];

if ($pedido_id <= 0 || !is_array($detalles) || count($detalles) === 0) {
  http_response_code(400);
  echo json_encode(['error' => 'Datos inválidos']);
  exit;
}

// Obtener estado del pedido y validar existencia
$stmt = $conn->prepare("SELECT estado FROM pedidos WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $pedido_id);
if (!$stmt->execute()) {
  http_response_code(500);
  echo json_encode(['error' => 'Error al consultar pedido']);
  exit;
}
$res = $stmt->get_result();
$row = $res->fetch_assoc();
if (!$row) {
  http_response_code(404);
  echo json_encode(['error' => 'Pedido no encontrado']);
  exit;
}

$estado = trim(strtolower((string)$row['estado']));
$noEditables = ['pagado','paid','completado','cerrado'];
if (in_array($estado, $noEditables, true)) {
  http_response_code(403);
  echo json_encode(['error' => "No se puede modificar un pedido en estado: {$row['estado']}"]);
  exit;
}

// Comenzar transacción
$conn->begin_transaction();

try {
  $updateStmt = $conn->prepare("UPDATE pedido_detalles SET cantidad = ? WHERE pedido_id = ? AND producto_id = ?");
  if (!$updateStmt) throw new Exception($conn->error);

  foreach ($detalles as $item) {
    $productoId = intval($item['producto_id'] ?? 0);
    $cantidadRaw = (float)($item['cantidad'] ?? 0);
    if ($productoId <= 0 || $cantidadRaw < 0) continue; // ignorar filas inválidas

    // Normalizar a 3 decimales; almacenar como decimal (cadena) para evitar pérdida
    $cantidad = number_format($cantidadRaw, 3, '.', '');
    $updateStmt->bind_param('sii', $cantidad, $pedido_id, $productoId);
    if (!$updateStmt->execute()) {
      throw new Exception("Error al actualizar producto {$productoId}: " . $updateStmt->error);
    }
  }

  // Recalcular total del pedido (evitar NULL con COALESCE)
  $res2 = $conn->query(
    "SELECT COALESCE(SUM(p.precio * d.cantidad), 0) AS total_pedido
     FROM pedido_detalles d
     JOIN productos p ON p.id = d.producto_id
     WHERE d.pedido_id = {$pedido_id}"
  );
  if (!$res2) throw new Exception($conn->error);
  $totalPedido = (float)$res2->fetch_assoc()['total_pedido'];

  // Recalcular total pagado (evitar NULL)
  $res3 = $conn->query("SELECT COALESCE(SUM(monto), 0) AS total_pagado FROM pagos WHERE pedido_id = {$pedido_id}");
  if (!$res3) throw new Exception($conn->error);
  $totalPagado = (float)$res3->fetch_assoc()['total_pagado'];

  // Actualizar estado
  $newEstado = ($totalPagado >= $totalPedido) ? 'pagado' : 'deudor';
  $stmtUpd = $conn->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
  $stmtUpd->bind_param('si', $newEstado, $pedido_id);
  if (!$stmtUpd->execute()) throw new Exception($stmtUpd->error);

  $conn->commit();

  http_response_code(200);
  echo json_encode([
    'mensaje' => 'Cantidades actualizadas',
    'estado' => $newEstado,
    'total_pedido' => $totalPedido,
    'total_pagado' => $totalPagado
  ]);
  exit;

} catch (Exception $e) {
  $conn->rollback();
  http_response_code(500);
  echo json_encode(['error' => 'Error al actualizar: ' . $e->getMessage()]);
  exit;
}
