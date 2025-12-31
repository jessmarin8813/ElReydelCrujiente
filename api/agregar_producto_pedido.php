<?php
require 'conexion.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido. Use POST.']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$pedido_id = intval($data['pedido_id'] ?? 0);
$producto_id = intval($data['producto_id'] ?? 0);
$cantidad = floatval($data['cantidad'] ?? 0);

if ($pedido_id <= 0 || $producto_id <= 0 || $cantidad <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Datos inválidos: pedido_id, producto_id y cantidad requeridos."]);
    exit;
}

$conn->begin_transaction();

try {
    // Verificar si ya existe ese producto en el pedido
    $stmtCheck = $conn->prepare("SELECT cantidad FROM pedido_detalles WHERE pedido_id = ? AND producto_id = ?");
    if (!$stmtCheck) throw new Exception("Error preparando check: " . $conn->error);
    
    $stmtCheck->bind_param("ii", $pedido_id, $producto_id);
    $stmtCheck->execute();
    $res = $stmtCheck->get_result();
    
    if ($row = $res->fetch_assoc()) {
        $stmtCheck->close();
        // Actualizar
        $actual = floatval($row['cantidad']);
        $nuevaCantidad = $actual + $cantidad;
        
        $stmtUpd = $conn->prepare("UPDATE pedido_detalles SET cantidad = ? WHERE pedido_id = ? AND producto_id = ?");
        if (!$stmtUpd) throw new Exception("Error preparando update: " . $conn->error);
        
        $stmtUpd->bind_param("dii", $nuevaCantidad, $pedido_id, $producto_id);
        $stmtUpd->execute();
        $stmtUpd->close();
    } else {
        $stmtCheck->close();
        // Insertar
        $stmtIns = $conn->prepare("INSERT INTO pedido_detalles (pedido_id, producto_id, cantidad) VALUES (?, ?, ?)");
        if (!$stmtIns) throw new Exception("Error preparando insert: " . $conn->error);
        
        $stmtIns->bind_param("iid", $pedido_id, $producto_id, $cantidad);
        $stmtIns->execute();
        $stmtIns->close();
    }

    $conn->commit();
    echo json_encode(["mensaje" => "Producto agregado correctamente"]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["error" => "Error al agregar producto al pedido: " . $e->getMessage()]);
}
?>
