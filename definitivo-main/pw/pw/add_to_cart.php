<?php
session_start();
require 'config.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$id_producto = $_POST['id_producto'];
$cantidad = $_POST['cantidad'];

// Verificar si el producto ya está en el carrito
$stmt = $conn->prepare("SELECT cantidad FROM carrito WHERE id_usuario = ? AND id_producto = ?");
$stmt->bind_param("ii", $id_usuario, $id_producto);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($current_quantity);

if ($stmt->num_rows > 0) {
    // Actualizar la cantidad si el producto ya está en el carrito
    $stmt->fetch();
    $nueva_cantidad = $current_quantity + $cantidad;
    $stmt = $conn->prepare("UPDATE carrito SET cantidad = ? WHERE id_usuario = ? AND id_producto = ?");
    $stmt->bind_param("iii", $nueva_cantidad, $id_usuario, $id_producto);
} else {
    // Insertar un nuevo registro en el carrito
    $stmt = $conn->prepare("INSERT INTO carrito (id_usuario, id_producto, cantidad) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $id_usuario, $id_producto, $cantidad);
}
$stmt->execute();
$stmt->close();
$conn->close();

header("location: ver_carrito.php");
exit;
?>
