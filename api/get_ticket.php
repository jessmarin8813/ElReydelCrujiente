<?php
// api/get_ticket.php
header('Content-Type: application/json; charset=utf-8');
include 'conexion.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de pedido inválido']);
    exit;
}

// 1. Obtener cabecera del pedido (incluyendo tasa_pedido)
$sqlPedido = "SELECT id, nombre, fecha, total_usd, tasa_pedido FROM pedidos WHERE id = ?";
$stmt = $conn->prepare($sqlPedido);
$stmt->bind_param("i", $id);
$stmt->execute();
$resPedido = $stmt->get_result();
$pedido = $resPedido->fetch_assoc();

if (!$pedido) {
    http_response_code(404);
    echo json_encode(['error' => 'Pedido no encontrado']);
    exit;
}

// Si tasa_pedido es null o 0 (pedidos viejos), usar la tasa actual como fallback o buscar la más cercana
// Por ahora, si es 0, buscamos la tasa actual.
$tasa = floatval($pedido['tasa_pedido']);
if ($tasa == 0) {
    $rts = $conn->query("SELECT tasa FROM tipo_cambio ORDER BY fecha DESC, id DESC LIMIT 1");
    if ($rts && $rt = $rts->fetch_assoc()) {
        $tasa = floatval($rt['tasa']);
    } else {
        $tasa = 1; // Fallback extremo
    }
}

// Calcular total Bs
$totalUsd = floatval($pedido['total_usd']);
$totalBs = $totalUsd * $tasa;

// 2. Obtener items
$sqlItems = "SELECT d.cantidad, p.nombre, p.precio 
             FROM pedido_detalles d
             JOIN productos p ON d.producto_id = p.id
             WHERE d.pedido_id = ?";
$stmtItems = $conn->prepare($sqlItems);
$stmtItems->bind_param("i", $id);
$stmtItems->execute();
$resItems = $stmtItems->get_result();

$items = [];
while ($row = $resItems->fetch_assoc()) {
    $items[] = [
        'cantidad' => floatval($row['cantidad']),
        'nombre' => $row['nombre'],
        'precio' => floatval($row['precio']), // Precio unitario en USD
        'subtotal' => floatval($row['cantidad']) * floatval($row['precio'])
    ];
}

// Respuesta
echo json_encode([
    'id' => $pedido['id'],
    'nombre' => $pedido['nombre'],
    'fecha' => $pedido['fecha'],
    'tasa' => $tasa,
    'total_usd' => $totalUsd,
    'total_bs' => $totalBs,
    'items' => $items
]);
?>
