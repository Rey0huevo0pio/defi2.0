<?php
session_start();
require 'config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

$metodo_pago = $_POST['metodo_pago'] ?? '';
$direccion_envio = $_POST['direccion_envio'] ?? '';
$codigo_cupon = $_POST['codigo_cupon'] ?? '';

// Obtener el id_pais de la dirección existente
$stmt = $conn->prepare("SELECT id_pais FROM envios WHERE id_envio = ?");
$stmt->bind_param("i", $direccion_envio);
$stmt->execute();
$result = $stmt->get_result();
$envio = $result->fetch_assoc();
$id_pais = $envio['id_pais'] ?? null;
$stmt->close();

// Obtener los detalles del carrito del usuario
$stmt = $conn->prepare("SELECT c.id_carrito, p.id_producto, p.nombre, p.precio, c.cantidad 
                        FROM carrito c 
                        JOIN productos p ON c.id_producto = p.id_producto 
                        WHERE c.id_usuario = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$carrito = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Verificar si el carrito no está vacío
if (empty($carrito)) {
    echo "El carrito está vacío. No se puede procesar el pedido.";
    exit;
}

$total_carrito = 0;
foreach ($carrito as $item) {
    $total_carrito += $item['precio'] * $item['cantidad'];
}

$descuento = 0;
$mensaje_cupon = '';
if (!empty($codigo_cupon)) {
    $stmt = $conn->prepare("SELECT id_cupon, descuento FROM cupones WHERE codigo = ? AND fecha_expiracion >= CURDATE() AND usos < limite_usos");
    $stmt->bind_param("s", $codigo_cupon);
    $stmt->execute();
    $result = $stmt->get_result();
    $cupon = $result->fetch_assoc();
    $stmt->close();

    if ($cupon) {
        $descuento = $cupon['descuento'];
    } else {
        $mensaje_cupon = "Cupón inválido o expirado. Se procederá sin descuento.";
    }
}

$total_con_descuento = $total_carrito - ($total_carrito * ($descuento / 100));

// Obtener el IVA y el costo de envío del país
$iva = 0;
$costo_envio = 0;

if ($id_pais) {
    $stmt = $conn->prepare("SELECT p.iva, ce.costo_envio 
                            FROM paises p
                            JOIN costos_envio ce ON p.id_pais = ce.id_pais
                            WHERE p.id_pais = ?");
    $stmt->bind_param("i", $id_pais);
    $stmt->execute();
    $result = $stmt->get_result();
    $pais_info = $result->fetch_assoc();
    $stmt->close();
    
    if ($pais_info) {
        $iva = $pais_info['iva'];
        $costo_envio = $pais_info['costo_envio'];
    }
}

$impuesto = $total_con_descuento * ($iva / 100);
$total_final = $total_con_descuento + $impuesto + $costo_envio;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>MayShop - Confirmar Compra</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./css/index.css">
</head>
<body>
    <header class="bg-primary text-white p-3">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-light">
                <a class="navbar-brand text-white" href="index.php">MayShop</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item"><a class="nav-link text-white" href="index.php">Inicio</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="products.php">Mis Productos</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="contact.php">Contacto</a></li>
                        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                            <li class="nav-item"><a class="nav-link text-white" href="favorites_page.php"><i class="fas fa-heart"></i></a></li>
                            <li class="nav-item"><a class="nav-link text-white" href="profile.php"><i class="fas fa-user-gear"></i></a></li>
                            <li class="nav-item"><a class="nav-link text-white" href="logout.php"><i class="fas fa-share-from-square"></i></a></li>
                        <?php else: ?>
                            <li class="nav-item"><a class="nav-link text-white" href="login.php">Iniciar sesión</a></li>
                            <li class="nav-item"><a class="nav-link text-white" href="register.php">Registrarse</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>
        </div>
    </header>

    <main class="container mt-4">
        <h2>Confirmar Compra</h2>
        <?php if (!empty($mensaje_cupon)): ?>
            <div class="alert alert-warning"><?php echo $mensaje_cupon; ?></div>
        <?php endif; ?>
        <form action="pedido_concluido.php" method="post">
            <input type="hidden" name="metodo_pago" value="<?php echo htmlspecialchars($metodo_pago); ?>">
            <input type="hidden" name="direccion_envio" value="<?php echo htmlspecialchars($direccion_envio); ?>">
            <input type="hidden" name="codigo_cupon" value="<?php echo htmlspecialchars($codigo_cupon); ?>">
            <h5>Productos en el Carrito</h5>
            <ul class="list-group mb-3">
                <?php foreach ($carrito as $item): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?php echo htmlspecialchars($item['nombre']); ?>
                        <span>$<?php echo number_format($item['precio'], 2); ?> x <?php echo $item['cantidad']; ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="cart-total">
                <p><strong>Total del Carrito:</strong> $<?php echo number_format($total_carrito, 2); ?></p>
                <p><strong>Descuento:</strong> -$<?php echo number_format($total_carrito * ($descuento / 100), 2); ?></p>
                <p><strong>Total con Descuento:</strong> $<?php echo number_format($total_con_descuento, 2); ?></p>
                <p><strong>IVA (<?php echo $iva; ?>%):</strong> $<?php echo number_format($impuesto, 2); ?></p>
                <p><strong>Costo de Envío:</strong> $<?php echo number_format($costo_envio, 2); ?></p>
                <p><strong>Total Final:</strong> $<span id="total-carrito"><?php echo number_format($total_final, 2); ?></span></p>
            </div>
            <div class="d-flex justify-content-between mt-3">
                <button type="button" class="btn btn-secondary" onclick="window.history.back();" style="background-color: red;">Atrás</button>
                <button type="submit" class="btn btn-primary">Realizar Pedido</button>
            </div>
        </form>
    </main>
</body>
</html>


