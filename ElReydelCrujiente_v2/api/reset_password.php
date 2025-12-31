<?php
require 'config.php';

try {
    $pass = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = 'admin'");
    $stmt->execute([$pass]);
    echo "Password for 'admin' reset to 'admin123'.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
