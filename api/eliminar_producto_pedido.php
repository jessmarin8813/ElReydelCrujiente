<?php
require 'conexion.php';
header('Content-Type: application/json');

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Método no permitido. Use POST.']);
  exit;
}

// Si usas sesiones/usuarios, habilita y valida sesión (opcional)
if (session_status() === PHP_SESSION_NONE) session_start();
// ejemplo: if (!isset($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'No autenticado']); exit; }

$raw = file_get_contents('php://input');
$input = json_decode($raw, true) ?? [];
$pedido_id = intval($input['pedido_id'] ?? 0);
$producto_id = intval($input['producto_id'] ?? 0);

// Log simple para depuración local (borra o comenta en producción)
$logLine = date('Y-m-d H:i:s') . " | eliminar_producto_pedido request: pedido_id=$pedido_id producto_id=$producto_id raw=" . substr($raw,0,1000) . PHP_EOL;
file_put_contents(__DIR__ . '/logs/eliminar_producto_debug.log', $logLine, FILE_APPEND);

if ($pedido_id <= 0 || $producto_id <= 0) {
  http_response_code(400);
  echo json_encode(['error' => 'Datos inválidos']);
  exit;
}

// Obtener estado del pedido de forma segura
$stmt = $conn->prepare("SELECT estado FROM pedidos WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $pedido_id);
if (!$stmt->execute()) {
  http_response_code(500);
  echo json_encode(['error' => 'Error de BD al consultar pedido']);
  exit;
}
$res = $stmt->get_result();
$row = $res->fetch_assoc();
if (!$row) {
  http_response_code(404);
  echo json_encode(['error' => 'Pedido no encontrado']);
  exit;
}

$estadoLeido = trim(strtolower((string)$row['estado']));

// Agregar traza al log para ver qué estado se leyó
file_put_contents(__DIR__ . '/logs/eliminar_producto_debug.log', date('Y-m-d H:i:s') . " | estado_leido='$estadoLeido'". PHP_EOL, FILE_APPEND);

// Si tu negocio define múltiples valores equivalentes, normaliza aquí
$estadosNoEditables = ['pagado', 'paid', 'completado', 'cerrado']; // añade variantes según tu BD
foreach ($estadosNoEditables as $v) {
  if ($estadoLeido === $v) {
    http_response_code(403);
    echo json_encode(['error' => 'No se puede modificar un pedido en estado: ' . $row['estado']]);
    exit;
  }
}

// Realizar eliminación con prepared statement
$stmtDel = $conn->prepare("DELETE FROM pedido_detalles WHERE pedido_id = ? AND producto_id = ?");
$stmtDel->bind_param('ii', $pedido_id, $producto_id);
if (!$stmtDel->execute()) {
  http_response_code(500);
  echo json_encode(['error' => 'No se pudo eliminar: ' . $stmtDel->error]);
  exit;
}

// Comprobar si realmente se eliminó alguna fila
if ($stmtDel->affected_rows === 0) {
  // No existía ese detalle (no se cambió nada)
  echo json_encode(['mensaje' => 'No se encontró el producto en el pedido (sin cambios)']);
  exit;
}

echo json_encode(['mensaje' => 'Producto eliminado correctamente']);
