<?php
require 'conexion.php';
header('Content-Type: application/json');

$res = $conn->query("SELECT id, nombre, precio, disponible FROM productos");
$productos = [];

while ($row = $res->fetch_assoc()) {
  $productos[] = [
    'id' => intval($row['id']),
    'nombre' => $row['nombre'],
    'precio' => floatval($row['precio']),
    'disponible' => intval($row['disponible'])
  ];
}

echo json_encode($productos);
