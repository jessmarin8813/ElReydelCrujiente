<?php
// api/products.php
require 'config.php';
require 'auth_middleware.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Public access: Get all active products
        try {
            $stmt = $pdo->query("SELECT * FROM products WHERE is_active = 1 ORDER BY category, name");
            $products = $stmt->fetchAll();
            echo json_encode($products);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'POST':
        // Admin/Kitchen only
        $user = authenticate();
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"));
        if (!isset($data->name) || !isset($data->price_usd) || !isset($data->category)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price_usd, category, image_url) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $data->name,
                $data->description ?? '',
                $data->price_usd,
                $data->category,
                $data->image_url ?? ''
            ]);
            echo json_encode(['id' => $pdo->lastInsertId(), 'message' => 'Product created']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'PUT':
        // Admin only
        $user = authenticate();
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"));
        if (!isset($data->id)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing product ID']);
            exit;
        }

        try {
            $sql = "UPDATE products SET name=?, description=?, price_usd=?, category=?, image_url=?, is_active=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $data->name,
                $data->description ?? '',
                $data->price_usd,
                $data->category,
                $data->image_url ?? '',
                isset($data->is_active) ? $data->is_active : 1,
                $data->id
            ]);
            echo json_encode(['message' => 'Product updated']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'DELETE':
        // Admin only (Soft delete)
        $user = authenticate();
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"));
        if (!isset($data->id)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing ID']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("UPDATE products SET is_active = 0 WHERE id = ?");
            $stmt->execute([$data->id]);
            echo json_encode(['message' => 'Product deleted (soft)']);
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
