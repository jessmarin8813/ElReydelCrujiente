<?php
require 'conexion.php';
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents("php://input"), true);
$id = intval($data['id'] ?? 0);
$nombre = trim($data['nombre'] ?? '');
$estado = $data['estado'] ?? 'activo';
$productos = $data['productos'] ?? [];

// Validaciones
if ($id <= 0) {
  http_response_code(400);
  echo json_encode(["error" => "ID de combo inválido"]);
  exit;
}
if (empty($nombre)) {
  http_response_code(400);
  echo json_encode(["error" => "Nombre requerido"]);
  exit;
}
if (!is_array($productos) || count($productos) === 0) {
  http_response_code(400);
  echo json_encode(["error" => "Se requieren productos para el combo"]);
  exit;
}
if (!in_array($estado, ['activo', 'inactivo'])) {
    $estado = 'activo';
}

$conn->begin_transaction();

try {
  // 🔹 Calcular precio total del combo
  $precio_total = 0;
  
  $stmtProd = $conn->prepare("SELECT precio FROM productos WHERE id = ?");
  if (!$stmtProd) throw new Exception("Error preparando select producto: " . $conn->error);

  foreach ($productos as $p) {
    $pid = intval($p['producto_id'] ?? 0);
    $cant = floatval($p['cantidad'] ?? 0);
    if ($pid <= 0 || $cant <= 0) continue;

    // Obtener precio del producto
    $stmtProd->bind_param("i", $pid);
    $stmtProd->execute();
    $res = $stmtProd->get_result();
    
    if ($row = $res->fetch_assoc()) {
      $precio_unitario = floatval($row['precio']);
      $precio_total += $precio_unitario * $cant;
    }
  }
  $stmtProd->close();

  // 🔹 Actualizar combo
  $stmtUpdate = $conn->prepare("UPDATE combos SET nombre = ?, precio = ?, estado = ? WHERE id = ?");
  if (!$stmtUpdate) throw new Exception("Error preparando update combo: " . $conn->error);
  $stmtUpdate->bind_param("sdsi", $nombre, $precio_total, $estado, $id);
  $stmtUpdate->execute();
  $stmtUpdate->close();

  // 🔹 Actualizar productos del combo: Primero borrar antiguos
  $stmtDel = $conn->prepare("DELETE FROM combo_productos WHERE combo_id = ?");
  if (!$stmtDel) throw new Exception("Error preparando delete combo_productos: " . $conn->error);
  $stmtDel->bind_param("i", $id);
  $stmtDel->execute();
  $stmtDel->close();

  // Insertar nuevos
  $stmtInsert = $conn->prepare("INSERT INTO combo_productos (combo_id, producto_id, cantidad) VALUES (?, ?, ?)");
  if (!$stmtInsert) throw new Exception("Error preparando insert combo_productos: " . $conn->error);

  foreach ($productos as $p) {
    $pid = intval($p['producto_id'] ?? 0);
    $cant = floatval($p['cantidad'] ?? 0);
    if ($pid > 0 && $cant > 0) {
        $stmtInsert->bind_param("iid", $id, $pid, $cant);
        $stmtInsert->execute();
    }
  }
  $stmtInsert->close();

  $conn->commit();
  echo json_encode(["mensaje" => "Combo actualizado correctamente", "precio_usd" => round($precio_total, 2)]);

} catch (Exception $e) {
  $conn->rollback();
  http_response_code(500);
  echo json_encode(["error" => "Error al actualizar combo: " . $e->getMessage()]);
}
?>
