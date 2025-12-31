<?php
// api/pedido.php
// Crea un pedido con detalles y guarda total_usd + total (Bs) usando tasa actual.
// Espera POST JSON: { tipo, nombre, productos: [{producto_id, cantidad}, ...], mesa_id? }

require 'conexion.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Método no permitido. Use POST.']);
  exit;
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true) ?? [];

$tipo = $input['tipo'] ?? '';
$nombre = $input['nombre'] ?? '';
$productos = $input['productos'] ?? [];
$mesa_id = isset($input['mesa_id']) && $input['mesa_id'] !== '' ? intval($input['mesa_id']) : null;

// validaciones simples
if (!is_array($productos) || count($productos) === 0) {
  http_response_code(400);
  echo json_encode(['error' => 'No hay productos en el pedido.']);
  exit;
}

// obtener tasa actual (última fila tipo_cambio)
$tasa_bcv = 1.0;
$rs = $conn->query("SELECT tasa FROM tipo_cambio ORDER BY fecha DESC, id DESC LIMIT 1");
if ($rs && $r = $rs->fetch_assoc()) {
  $tasa_bcv = (float)$r['tasa'];
}
if ($tasa_bcv <= 0) $tasa_bcv = 1.0;

// calcular total_usd leyendo precios en tabla productos (se asume precio en USD)
$total_usd = 0.0;
foreach ($productos as $p) {
  $prod_id = intval($p['producto_id'] ?? 0);
  $cant = (float)($p['cantidad'] ?? 0);
  if ($prod_id <= 0 || $cant <= 0) continue;

  $stmtP = $conn->prepare("SELECT precio FROM productos WHERE id = ?");
  if (!$stmtP) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al preparar consulta producto: '.$conn->error]);
    exit;
  }
  $stmtP->bind_param('i', $prod_id);
  $stmtP->execute();
  $rsP = $stmtP->get_result();
  $rowP = $rsP->fetch_assoc();
  $precio = (float)($rowP['precio'] ?? 0.0);
  $total_usd += $precio * $cant;
}

// calcular total en Bs para compatibilidad (columna total)
$total_bs = round($total_usd * $tasa_bcv, 2);

$conn->begin_transaction();

try {
  // preparar INSERT en pedidos usando las columnas que tienes: total, total_usd, tasa_pedido
  // manejar mesa_id NULL con dos ramas para bind_param
  if ($mesa_id === null) {
    $ins = $conn->prepare("INSERT INTO pedidos (nombre, tipo, mesa_id, total, total_usd, tasa_pedido, estado, fecha_creacion)
                           VALUES (?, ?, NULL, ?, ?, ?, 'deudor', NOW())");
    if (!$ins) throw new Exception('Error preparando insert pedido: '.$conn->error);
    // bind: nombre (s), tipo (s), total (d), total_usd (d), tasa_pedido (d)
    $ins->bind_param('ssddd', $nombre, $tipo, $total_bs, $total_usd, $tasa_bcv);
  } else {
    $ins = $conn->prepare("INSERT INTO pedidos (nombre, tipo, mesa_id, total, total_usd, tasa_pedido, estado, fecha_creacion)
                           VALUES (?, ?, ?, ?, ?, ?, 'deudor', NOW())");
    if (!$ins) throw new Exception('Error preparando insert pedido: '.$conn->error);
    // bind: nombre (s), tipo (s), mesa_id (i), total (d), total_usd (d), tasa_pedido (d)
    $ins->bind_param('ssisdd', $nombre, $tipo, $mesa_id, $total_bs, $total_usd, $tasa_bcv);
  }

  if (!$ins->execute()) throw new Exception('Error insert pedido: '.$ins->error);

  $pedido_id = $conn->insert_id;

  // insertar detalles (pedido_detalles)
  $insDet = $conn->prepare("INSERT INTO pedido_detalles (pedido_id, producto_id, cantidad) VALUES (?, ?, ?)");
  if (!$insDet) throw new Exception('Error preparando insert detalle: '.$conn->error);

  foreach ($productos as $p) {
    $prod_id = intval($p['producto_id'] ?? 0);
    $cant = (float)($p['cantidad'] ?? 0);
    if ($prod_id <= 0 || $cant <= 0) continue;
    // bind types: i = int, i = int, d = double (cantidad decimal)
    $insDet->bind_param('iid', $pedido_id, $prod_id, $cant);
    if (!$insDet->execute()) throw new Exception('Error insert detalle: '.$insDet->error);
  }

  $conn->commit();

  http_response_code(201);
  echo json_encode([
    'ok' => true,
    'mensaje' => 'Pedido creado',
    'pedido_id' => $pedido_id,
    'total_pedido_usd' => round($total_usd,2),
    'total_pedido_bs' => $total_bs,
    'tasa_pedido' => $tasa_bcv
  ]);
  exit;

} catch (Exception $e) {
  $conn->rollback();
  $code = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500;
  http_response_code($code);
  echo json_encode(['error' => 'Error al crear pedido: '.$e->getMessage()]);
  exit;
}
