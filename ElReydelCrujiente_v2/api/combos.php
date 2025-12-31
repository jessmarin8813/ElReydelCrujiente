<?php
// api/combos.php
require 'config.php';
require 'auth_middleware.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Public: Get all active combos with their items
        try {
            $stmt = $pdo->query("SELECT * FROM combos WHERE is_active = 1");
            $combos = $stmt->fetchAll();

            // Fetch items for each combo
            foreach ($combos as &$combo) {
                $stmtItems = $pdo->prepare("
                    SELECT ci.quantity, p.name, p.id as product_id 
                    FROM combo_items ci 
                    JOIN products p ON ci.product_id = p.id 
                    WHERE ci.combo_id = ?
                ");
                $stmtItems->execute([$combo['id']]);
                $combo['items'] = $stmtItems->fetchAll();
            }
            echo json_encode($combos);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'POST':
        // Admin only
        $user = authenticate();
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['name']) || !isset($data['price_usd']) || !isset($data['items'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO combos (name, description, price_usd) VALUES (?, ?, ?)");
            $stmt->execute([$data['name'], $data['description'] ?? '', $data['price_usd']]);
            $comboId = $pdo->lastInsertId();

            $stmtItem = $pdo->prepare("INSERT INTO combo_items (combo_id, product_id, quantity) VALUES (?, ?, ?)");
            foreach ($data['items'] as $item) {
                $stmtItem->execute([$comboId, $item['product_id'], $item['quantity']]);
            }

            $pdo->commit();
            echo json_encode(['id' => $comboId, 'message' => 'Combo created']);
        } catch (Exception $e) {
            $pdo->rollBack();
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
