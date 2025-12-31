<?php
$host = "localhost";
$usuario = "root";
$clave = "";
$bd = "elreydelcrujiente";

// Crear conexión
$conn = new mysqli($host, $usuario, $clave, $bd);

// Verificar conexión
if ($conn->connect_error) {
  // En producción, no mostrar el error específico al usuario si contiene datos sensibles
  die(json_encode(["error" => "Error de conexión a la base de datos"]));
}

// Establecer charset UTF-8
if (!$conn->set_charset("utf8mb4")) {
    error_log("Error cargando el conjunto de caracteres utf8mb4: " . $conn->error);
}
?>
