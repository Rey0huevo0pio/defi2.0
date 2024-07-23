<?php
session_start();
require 'config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$metodo_pago = $_POST['metodo_pago'];
$direccion_envio = $_POST['direccion_envio'];
$codigo_cupon = $_POST['codigo_cupon'] ?? '';
$fecha_pedido = date("Y-m-d H:i:s");
$estado = "Pendiente";

// Obtener los productos en el carrito del usuario
$stmt = $conn->prepare("SELECT c.id_carrito, p.id_producto, p.precio, c.cantidad 
                        FROM carrito c 
                        JOIN productos p ON c.id_producto = p.id_producto 
                        WHERE c.id_usuario = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$carrito = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total = 0;
foreach ($carrito as $item) {
    $total += $item['precio'] * $item['cantidad'];
}

// Verificar y aplicar cupón si se envió
$descuento = 0;
$total_con_descuento = $total;
if (!empty($codigo_cupon)) {
    $stmt = $conn->prepare("SELECT id_cupon, descuento FROM cupones WHERE codigo = ? AND fecha_expiracion >= CURDATE() AND usos < limite_usos");
    $stmt->bind_param("s", $codigo_cupon);
    $stmt->execute();
    $result = $stmt->get_result();
    $cupon = $result->fetch_assoc();
    $stmt->close();

    if ($cupon) {
        $descuento = $cupon['descuento'];
        $total_con_descuento = $total - ($total * ($descuento / 100));

        // Incrementar el uso del cupón
        $stmt = $conn->prepare("UPDATE cupones SET usos = usos + 1 WHERE id_cupon = ?");
        $stmt->bind_param("i", $cupon['id_cupon']);
        $stmt->execute();
        $stmt->close();
    }
}

// Insertar el pedido en la base de datos
$stmt = $conn->prepare("INSERT INTO pedidos (id_usuario, fecha_pedido, estado, total, metodo_pago) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("issds", $id_usuario, $fecha_pedido, $estado, $total_con_descuento, $metodo_pago);
$stmt->execute();
$id_pedido = $stmt->insert_id;
$stmt->close();

// Insertar los detalles del pedido
foreach ($carrito as $item) {
    $stmt = $conn->prepare("INSERT INTO pedido_productos (id_pedido, id_producto, cantidad, precio) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiid", $id_pedido, $item['id_producto'], $item['cantidad'], $item['precio']);
    $stmt->execute();
    $stmt->close();
}

// Actualizar la tabla de envíos con el id_pedido
$stmt = $conn->prepare("UPDATE envios SET id_pedido = ? WHERE id_envio = ?");
$stmt->bind_param("ii", $id_pedido, $direccion_envio);
$stmt->execute();
$stmt->close();

// Vaciar el carrito
$stmt = $conn->prepare("DELETE FROM carrito WHERE id_usuario = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$stmt->close();

echo "<script>alert('Pedido realizado con éxito.'); window.location.href='index.php';</script>";

$conn->close();
?>
