<?php
$servername = "localhost";
$username = "id21147976_mingoyo";
$password = "Martes12345.";
$dbname = "id21147976_joyeria";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
