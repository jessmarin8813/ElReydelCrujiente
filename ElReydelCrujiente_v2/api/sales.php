<?php
// api/sales.php
require 'config.php';
require 'auth_middleware.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // Record Payment (Cashier/Admin)
        $user = authenticate();
        
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['order_id']) || !isset($data['amount_paid']) || !isset($data['method'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing payment details']);
            exit;
        }

        try {
            $pdo->beginTransaction();

            // Get Order
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
            $stmt->execute([$data['order_id']]);
            $order = $stmt->fetch();

            if (!$order) {
                http_response_code(404);
                echo json_encode(['error' => 'Order not found']);
                exit;
            }

            // Insert Payment
            $currency = $data['currency'] ?? 'USD';
            $exchange_rate = $data['exchange_rate'] ?? 1.0; // Should fetch current rate
            
            // Calculate equivalent in USD for tracking
            $amount_usd = ($currency === 'VES') ? ($data['amount_paid'] / $exchange_rate) : $data['amount_paid'];

            $stmtPay = $pdo->prepare("INSERT INTO payments (order_id, method, amount_paid, currency, exchange_rate, amount_usd) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtPay->execute([
                $data['order_id'],
                $data['method'],
                $data['amount_paid'],
                $currency,
                $exchange_rate,
                $amount_usd
            ]);

            // Update Order Status to 'completed' if fully paid (simplified logic)
            // In real app, check sum of payments vs total
            $stmtUpd = $pdo->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
            $stmtUpd->execute([$data['order_id']]);

            $pdo->commit();
            echo json_encode(['message' => 'Payment recorded', 'status' => 'completed']);

        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'GET':
        // Get Daily Sales Report (Admin)
        $user = authenticate();
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit;
        }

        try {
            $date = $_GET['date'] ?? date('Y-m-d');
            
            $stmt = $pdo->prepare("
                SELECT method, currency, SUM(amount_paid) as total_amount, SUM(amount_usd) as total_usd 
                FROM payments 
                WHERE DATE(created_at) = ? 
                GROUP BY method, currency
            ");
            $stmt->execute([$date]);
            $report = $stmt->fetchAll();
            
            echo json_encode(['date' => $date, 'report' => $report]);
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
