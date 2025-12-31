<?php
// api/seed_data.php
require 'config.php';

try {
    $pdo->beginTransaction();

    // Check if products exist
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    if ($stmt->fetchColumn() > 0) {
        echo "Data already seeded.";
        exit;
    }

    // Insert Products
    $products = [
        ['Hamburguesa Clásica', 'Carne, queso, lechuga, tomate', 5.00, 'burgers'],
        ['Hamburguesa Doble', 'Doble carne, doble queso, bacon', 8.00, 'burgers'],
        ['Pepito Mixto', 'Carne, pollo, queso, salsas', 9.50, 'pepitos'],
        ['Refresco 1.5L', 'Coca-Cola o Pepsi', 2.50, 'drinks'],
        ['Papas Fritas', 'Ración grande', 3.00, 'sides']
    ];

    $stmt = $pdo->prepare("INSERT INTO products (name, description, price_usd, category) VALUES (?, ?, ?, ?)");
    foreach ($products as $p) {
        $stmt->execute($p);
    }

    // Insert Combo
    $stmtCombo = $pdo->prepare("INSERT INTO combos (name, description, price_usd) VALUES (?, ?, ?)");
    $stmtCombo->execute(['Combo Familiar', '2 Hamburguesas Clásicas + 1 Refresco + 1 Papas', 14.00]);
    $comboId = $pdo->lastInsertId();

    // Get Product IDs
    $pIds = [];
    $stmtGet = $pdo->query("SELECT id, name FROM products");
    while ($row = $stmtGet->fetch()) {
        $pIds[$row['name']] = $row['id'];
    }

    // Insert Combo Items
    $stmtItems = $pdo->prepare("INSERT INTO combo_items (combo_id, product_id, quantity) VALUES (?, ?, ?)");
    $stmtItems->execute([$comboId, $pIds['Hamburguesa Clásica'], 2]);
    $stmtItems->execute([$comboId, $pIds['Refresco 1.5L'], 1]);
    $stmtItems->execute([$comboId, $pIds['Papas Fritas'], 1]);

    $pdo->commit();
    echo "Data seeded successfully!";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error seeding data: " . $e->getMessage();
}
?>
