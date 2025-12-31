<?php
// api/orders.php
require 'config.php';
require 'auth_middleware.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Kitchen/Admin/Cashier: List orders
        // Optional filters: status, date
        $status = $_GET['status'] ?? null;
        
        try {
            $sql = "SELECT * FROM orders";
            $params = [];
            if ($status) {
                $sql .= " WHERE status = ?";
                $params[] = $status;
            }
            $sql .= " ORDER BY created_at DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $orders = $stmt->fetchAll();

            // Fetch items for each order
            foreach ($orders as &$order) {
                $stmtItems = $pdo->prepare("
                    SELECT oi.quantity, p.name as product_name, c.name as combo_name
                    FROM order_items oi
                    LEFT JOIN products p ON oi.product_id = p.id
                    LEFT JOIN combos c ON oi.combo_id = c.id
                    WHERE oi.order_id = ?
                ");
                $stmtItems->execute([$order['id']]);
                $order['items'] = $stmtItems->fetchAll();

                // Fetch payments for each order
                $stmtPayments = $pdo->prepare("SELECT * FROM payments WHERE order_id = ?");
                $stmtPayments->execute([$order['id']]);
                $order['payments'] = $stmtPayments->fetchAll();
            }
            echo json_encode($orders);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'POST':
        // Create Order (Public/Cashier)
        // Ideally authenticated, but for now let's allow it or require auth.
        // Let's require auth for consistency, or maybe 'guest' for self-service?
        // For this system, let's assume Cashier creates it.
        
        // $user = authenticate(); // Uncomment to enforce auth

        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['items']) || empty($data['items'])) {
            http_response_code(400);
            echo json_encode(['error' => 'No items in order']);
            exit;
        }

        try {
            $pdo->beginTransaction();

            // Calculate totals
            $total_usd = 0;
            $items_to_insert = [];

            foreach ($data['items'] as $item) {
                if (isset($item['product_id'])) {
                    $stmtP = $pdo->prepare("SELECT price_usd FROM products WHERE id = ?");
                    $stmtP->execute([$item['product_id']]);
                    $price = $stmtP->fetchColumn();
                    if ($price) {
                        $total_usd += $price * $item['quantity'];
                        $items_to_insert[] = [
                            'product_id' => $item['product_id'],
                            'combo_id' => null,
                            'quantity' => $item['quantity'],
                            'price' => $price
                        ];
                    }
                }
                // Handle combos similarly if needed
            }

            // Get Exchange Rate (Mock for now, should be from DB)
            $exchange_rate = 45.00; // Example
            $total_bs = $total_usd * $exchange_rate;

            $stmt = $pdo->prepare("INSERT INTO orders (table_number, total_usd, total_bs, exchange_rate, status) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->execute([$data['table_number'] ?? 'Takeout', $total_usd, $total_bs, $exchange_rate]);
            $orderId = $pdo->lastInsertId();

            $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, combo_id, quantity, price_at_time_usd) VALUES (?, ?, ?, ?, ?)");
            foreach ($items_to_insert as $item) {
                $stmtItem->execute([$orderId, $item['product_id'], $item['combo_id'], $item['quantity'], $item['price']]);
            }

            $pdo->commit();
            echo json_encode(['id' => $orderId, 'message' => 'Order created']);

        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'PUT':
        // Update Status (Kitchen/Admin)
        $user = authenticate(); // Require auth for status updates
        
        $data = json_decode(file_get_contents("php://input"));
        if (!isset($data->id) || !isset($data->status)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing ID or Status']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$data->status, $data->id]);
            echo json_encode(['message' => 'Order status updated']);
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
