<?php
// api/auth.php
session_start();
include 'conexion.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'login') {
    $input = json_decode(file_get_contents('php://input'), true);
    $user = $input['usuario'] ?? '';
    $pass = $input['clave'] ?? '';

    if (!$user || !$pass) {
        echo json_encode(['error' => 'Usuario y clave son requeridos']);
        exit;
    }

    $stmt = $conn->prepare("SELECT id, usuario, clave, rol FROM usuarios WHERE usuario = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        if (password_verify($pass, $row['clave'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['usuario'] = $row['usuario'];
            $_SESSION['rol'] = $row['rol'];
            echo json_encode(['success' => true, 'mensaje' => 'Bienvenido', 'rol' => $row['rol']]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Credenciales inválidas']);
        }
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Usuario no encontrado']);
    }
    exit;
}

if ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'check') {
    if (isset($_SESSION['user_id'])) {
        echo json_encode(['logged_in' => true, 'usuario' => $_SESSION['usuario'], 'rol' => $_SESSION['rol']]);
    } else {
        echo json_encode(['logged_in' => false]);
    }
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Acción no válida']);
?>
