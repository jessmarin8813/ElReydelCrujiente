<?php
include 'conexion.php';
$res = $conn->query("DESCRIBE productos");
if($res) {
    while($row = $res->fetch_assoc()) {
        print_r($row);
        echo "<br>";
    }
} else {
    echo "Error describing table: " . $conn->error;
}

echo "<hr>";
$res2 = $conn->query("DESCRIBE pedido_detalles");
if($res2) {
    while($row = $res2->fetch_assoc()) {
        print_r($row);
        echo "<br>";
    }
}
?>
