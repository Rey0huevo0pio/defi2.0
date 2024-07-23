<?php
session_start();
require 'config.php';

// Obtener el término de búsqueda
$termino_busqueda = isset($_GET['query']) ? $_GET['query'] : '';

// Buscar productos en la base de datos
$stmt = $conn->prepare("SELECT p.id_producto, p.nombre, p.descripcion, p.precio, p.imagenes, p.disponibilidad, u.nombre as usuario 
                        FROM productos p 
                        JOIN Usuarios u ON p.id_usuario = u.id_usuario
                        WHERE p.nombre LIKE ? OR p.descripcion LIKE ?");
$like_termino = '%' . $termino_busqueda . '%';
$stmt->bind_param("ss", $like_termino, $like_termino);
$stmt->execute();
$result = $stmt->get_result();
$productos = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados de Búsqueda - MayShop</title>
    <link rel="icon" href="./banners/logo.webp" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./css/buscar.css">
</head>
<body>
    <header>
        <nav>
        <div class="nav-middle">
            <div class="logo">
                <a class="nav_a" href="index.php">MayShop</a>
            </div>
            <div class="search-container">
                <form class="ib" method="GET" action="buscar.php">
                    <input type="search" name="query" placeholder="Buscar productos" aria-label="Buscar">
                    <button type="submit">Buscar</button>
                </form>
            </div>
            <div class="user-options">
                <a href="index.php">Inicio<span></span></a>
                <a href="products.php">Mis Productos<span></span></a>
                <a href="contact.php">Contacto<span></span></a>
                <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                    <a href="favorites_page.php"><i class="fas fa-heart"></i></a>
                    <a href="profile.php"><i class="fas fa-user-gear"></i></a>
                    <a href="ver_carrito.php"><i class="fa-solid fa-cart-shopping"></i></a>
                    <a href="logout.php"><i class="fas fa-share-from-square"></i></a>
                <?php else: ?>
                    <a href="login.php">Iniciar sesión<span></span></a>
                    <a href="register.php">Registrarse<span></span></a>
                <?php endif; ?>
            </div>
        </div>
        </nav>
    </header>

    <main class="container mt-4">
        <h2>Resultados de Búsqueda</h2>
        <?php if (empty($productos)): ?>
            <p>No se encontraron productos.</p>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($productos as $producto): ?>
                    <div class="product">
                        <a href="detalle_producto.php?id_producto=<?php echo $producto['id_producto']; ?>" class="card-link">
                            <div class="product-card">
                                <img src="<?php echo htmlspecialchars($producto['imagenes']); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>" class="card-img-top">
                                <div class="product-info">
                                    <h5><?php echo htmlspecialchars($producto['nombre']); ?></h5>
                                    <p><strong>Disponibles:</strong> <?php echo htmlspecialchars($producto['disponibilidad']); ?></p>
                                    <p><strong>Vendedor:</strong> <?php echo htmlspecialchars($producto['usuario']); ?></p>
                                    <p>$<?php echo number_format($producto['precio'], 2); ?></p>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
</body>
</html>
