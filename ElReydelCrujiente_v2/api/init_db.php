<?php
// api/init_db.php
$host = "localhost";
$user = "root";
$password = "";

try {
    // Connect without DB first to create it
    $pdo = new PDO("mysql:host=$host", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = file_get_contents(__DIR__ . '/../database/schema.sql');
    
    // Split by semicolon to execute multiple statements (basic parser)
    // PDO::exec doesn't support multiple statements in one go reliably across all drivers/configs without specific settings
    // But for simple schema imports, we can try executing the whole block or splitting.
    // Let's try splitting by command.
    
    $pdo->exec($sql);
    
    echo "Database initialized successfully!";

} catch (PDOException $e) {
    echo "Error initializing database: " . $e->getMessage();
}
?>
