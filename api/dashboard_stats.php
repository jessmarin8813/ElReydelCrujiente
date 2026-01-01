<?php
// api/dashboard_stats.php
header('Content-Type: application/json');
include 'conexion.php';

$response = [
    'ventas_semanales' => [],
    'top_productos' => [],
    'metodos_pago' => [],
    'resumen_hoy' => 0,
    'resumen_mes' => 0
];

// 1. Ventas últimos 7 días
$sql = "SELECT DATE(fecha) as fecha, SUM(total_pedido_usd) as total 
        FROM pedidos 
        WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
        AND estado = 'pagado'
        GROUP BY DATE(fecha) 
        ORDER BY fecha ASC";
$res = $conn->query($sql);
while($row = $res->fetch_assoc()) {
    $response['ventas_semanales'][] = $row;
}

// 2. Top 5 Productos (Histórico)
// Usamos alias pd para detalle y p para producto si fuera join, pero aqui detalle tiene nombre
$sql = "SELECT nombre, SUM(cantidad) as total_qty 
        FROM pedido_detalles 
        GROUP BY nombre 
        ORDER BY total_qty DESC 
        LIMIT 5";
$res = $conn->query($sql);
while($row = $res->fetch_assoc()) {
    $response['top_productos'][] = $row;
}

// 3. Métodos de Pago (Histórico)
$sql = "SELECT metodo, SUM(monto) as total 
        FROM pagos 
        GROUP BY metodo";
$res = $conn->query($sql);
while($row = $res->fetch_assoc()) {
    $response['metodos_pago'][] = $row;
}

// 4. Totales Rápidos (Hoy y Este Mes)
// Hoy
$sql = "SELECT SUM(total_pedido_usd) as total FROM pedidos WHERE DATE(fecha) = CURDATE() AND estado = 'pagado'";
$row = $conn->query($sql)->fetch_assoc();
$response['resumen_hoy'] = floatval($row['total'] ?? 0);

// Mes
$sql = "SELECT SUM(total_pedido_usd) as total FROM pedidos WHERE MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE()) AND estado = 'pagado'";
$row = $conn->query($sql)->fetch_assoc();
$response['resumen_mes'] = floatval($row['total'] ?? 0);

echo json_encode($response);
?>
