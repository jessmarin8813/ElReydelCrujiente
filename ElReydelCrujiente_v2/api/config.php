<?php
// api/config.php

// Allow CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$host = "localhost";
$user = "root";
$password = "";
$dbname = "elreydelcrujiente_v2";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If database doesn't exist, try to connect without dbname to create it (manual step usually, but handling error)
    if ($e->getCode() == 1049) {
        die(json_encode(["error" => "Database not found. Please import database/schema.sql"]));
    }
    die(json_encode(["error" => "Connection failed: " . $e->getMessage()]));
}
?>
