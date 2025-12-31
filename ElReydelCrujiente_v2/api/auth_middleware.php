<?php
// api/auth_middleware.php
require_once 'jwt_helper.php';

function authenticate() {
    $headers = apache_request_headers();
    $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
    
    if (!$authHeader) {
        http_response_code(401);
        echo json_encode(['error' => 'No token provided']);
        exit;
    }

    $token = str_replace('Bearer ', '', $authHeader);
    $payload = JWT::decode($token);

    if (!$payload) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid or expired token']);
        exit;
    }

    return $payload;
}
?>
