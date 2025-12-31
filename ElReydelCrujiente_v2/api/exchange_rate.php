<?php
// api/exchange_rate.php
require 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get current BCV rate (public endpoint)
        try {
            $stmt = $pdo->prepare("SELECT rate, created_at FROM daily_rates ORDER BY id DESC LIMIT 1");
            $stmt->execute();
            $result = $stmt->fetch();
            
            if ($result) {
                echo json_encode([
                    'rate' => floatval($result['rate']),
                    'updated_at' => $result['created_at']
                ]);
            } else {
                // Default rate if none exists
                echo json_encode([
                    'rate' => 45.50,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'POST':
        // Update BCV rate (admin only)
        require 'auth_middleware.php';
        $user = authenticate();
        
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden: Admin access required']);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['rate']) || !is_numeric($data['rate']) || $data['rate'] <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid rate value']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO daily_rates (rate, source, updated_by) VALUES (?, 'BCV', ?)");
            $stmt->execute([$data['rate'], $user['id']]);
            
            echo json_encode([
                'message' => 'Exchange rate updated successfully',
                'rate' => floatval($data['rate']),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
        break;
}
?>
