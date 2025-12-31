<?php
require 'conexion.php';
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents("php://input"), true);
$nombre = trim($data['nombre'] ?? '');
$productos = $data['productos'] ?? [];

if (empty($nombre) || !is_array($productos) || count($productos) === 0) {
  http_response_code(400);
  echo json_encode(["error" => "Datos incompletos: Se requiere nombre y productos."]);
  exit;
}

$conn->begin_transaction();

try {
  // 🔹 Insertar combo con precio temporal
  $stmtInsert = $conn->prepare("INSERT INTO combos (nombre, precio) VALUES (?, 0)");
  if (!$stmtInsert) throw new Exception("Error preparando insert combo: " . $conn->error);
  $stmtInsert->bind_param("s", $nombre);
  $stmtInsert->execute();
  $combo_id = $conn->insert_id;
  $stmtInsert->close();

  // 🔹 Calcular precio total e insertar productos
  $precio_total = 0;
  
  $stmtProd = $conn->prepare("SELECT precio FROM productos WHERE id = ?");
  $stmtRel = $conn->prepare("INSERT INTO combo_productos (combo_id, producto_id, cantidad) VALUES (?, ?, ?)");
  
  if (!$stmtProd || !$stmtRel) throw new Exception("Error preparando consultas de productos: " . $conn->error);

  foreach ($productos as $p) {
    // Validar cantidad y id
    $pid = intval($p['producto_id'] ?? 0);
    $cant = floatval($p['cantidad'] ?? 0);
    if ($pid <= 0 || $cant <= 0) continue;

    // Obtener precio
    $stmtProd->bind_param("i", $pid);
    $stmtProd->execute();
    $res = $stmtProd->get_result();
    
    if ($row = $res->fetch_assoc()) {
      $precio_unitario = floatval($row['precio']);
      $precio_total += $precio_unitario * $cant;
    }
    $res->close();

    // Insertar relación
    $stmtRel->bind_param("iid", $combo_id, $pid, $cant);
    $stmtRel->execute();
  }
  
  $stmtProd->close();
  $stmtRel->close();

  // 🔹 Actualizar precio final del combo
  $stmtUpdate = $conn->prepare("UPDATE combos SET precio = ? WHERE id = ?");
  if (!$stmtUpdate) throw new Exception("Error preparando update precio: " . $conn->error);
  $stmtUpdate->bind_param("di", $precio_total, $combo_id);
  $stmtUpdate->execute();
  $stmtUpdate->close();

  // 🔹 Obtener tasa actual para calcular precio en Bs
  $tasa = 1.0;
  $resTasa = $conn->query("SELECT tasa FROM tipo_cambio ORDER BY id DESC LIMIT 1"); // Query simple sin params es segura si no hay vars
  if ($resTasa && $rowTasa = $resTasa->fetch_assoc()) {
    $tasa = floatval($rowTasa['tasa']);
  }
  $precio_bs = round($precio_total * $tasa, 2);

  $conn->commit();
  
  http_response_code(201);
  echo json_encode([
    "mensaje" => "Combo creado exitosamente",
    "combo_id" => $combo_id,
    "precio_usd" => round($precio_total, 2),
    "precio_bs" => $precio_bs
  ]);

} catch (Exception $e) {
  $conn->rollback();
  http_response_code(500);
  echo json_encode(["error" => "Error al crear combo: " . $e->getMessage()]);
}
?>
