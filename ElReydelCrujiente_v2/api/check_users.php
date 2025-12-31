<?php
require 'config.php';

try {
    $stmt = $pdo->query("SELECT * FROM users WHERE username = 'admin'");
    $user = $stmt->fetch();

    if ($user) {
        echo "User 'admin' exists.\n";
        // We can't recover the password from the hash, but we can reset it if needed.
        // For now, just confirming existence.
        echo "ID: " . $user['id'] . "\n";
        echo "Role: " . $user['role'] . "\n";
    } else {
        echo "User 'admin' not found. Creating...\n";
        $pass = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
        $stmt->execute(['admin', $pass, 'admin']);
        echo "User 'admin' created with password 'admin123'.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
