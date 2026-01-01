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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'change_password') {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'No autorizado']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $actual = $input['actual'] ?? '';
    $nueva = $input['nueva'] ?? '';

    if (!$actual || !$nueva) {
        echo json_encode(['error' => 'Faltan datos']);
        exit;
    }

    $id = $_SESSION['user_id'];
    // Verificar clave actual
    $stmt = $conn->prepare("SELECT clave FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($row = $res->fetch_assoc()) {
        if (password_verify($actual, $row['clave'])) {
            // Actualizar
            $hash = password_hash($nueva, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE usuarios SET clave = ? WHERE id = ?");
            $update->bind_param("si", $hash, $id);
            if ($update->execute()) {
                echo json_encode(['success' => true, 'mensaje' => 'Contraseña actualizada correctamente']);
            } else {
                echo json_encode(['error' => 'Error al guardar en base de datos']);
            }
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'La contraseña actual no es correcta']);
        }
    } else {
        echo json_encode(['error' => 'Usuario no encontrado']);
    }
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Acción no válida']);
?>
