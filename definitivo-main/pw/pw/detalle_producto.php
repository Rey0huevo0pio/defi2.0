<?php
session_start();
require 'config.php';

// Obtener el ID del producto desde la URL
$id_producto = isset($_GET['id_producto']) ? intval($_GET['id_producto']) : 0;
$is_favorite = false;

if ($id_producto > 0) {
    // Obtener los detalles del producto desde la base de datos
    $stmt = $conn->prepare("SELECT p.id_producto, p.nombre, p.descripcion, p.precio, p.imagenes, p.disponibilidad, u.nombre as usuario 
                            FROM productos p 
                            JOIN Usuarios u ON p.id_usuario = u.id_usuario
                            WHERE p.id_producto = ?");
    $stmt->bind_param("i", $id_producto);
    $stmt->execute();
    $result = $stmt->get_result();
    $producto = $result->fetch_assoc();
    $stmt->close();

    // Decodificar las imágenes JSON
    $imagenes = json_decode($producto['imagenes']);

    // Verificar si el producto ya está en favoritos
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
        $userId = $_SESSION['id_usuario'];
        $stmt = $conn->prepare("SELECT * FROM favoritos WHERE id_usuario = ? AND id_producto = ?");
        $stmt->bind_param("ii", $userId, $id_producto);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $is_favorite = true;
        }
        $stmt->close();
    }

    // Obtener productos relacionados (aquí puedes definir la lógica para seleccionar productos relacionados)
    $stmt = $conn->prepare("SELECT id_producto, nombre, precio, imagenes FROM productos WHERE id_producto != ? LIMIT 5");
    $stmt->bind_param("i", $id_producto);
    $stmt->execute();
    $result = $stmt->get_result();
    $productos_relacionados = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Obtener opiniones del producto
    $stmt = $conn->prepare("SELECT o.calificacion, o.opinion, o.imagen, u.nombre as usuario 
                            FROM opiniones o 
                            JOIN Usuarios u ON o.id_usuario = u.id_usuario 
                            WHERE o.id_producto = ?");
    $stmt->bind_param("i", $id_producto);
    $stmt->execute();
    $result = $stmt->get_result();
    $opiniones = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle del Producto - MayShop</title>
    <link rel="icon" href="./banners/logo.webp" type="image/x-icon">
    <link rel="stylesheet" href="./css/detalle_producto.css">
</head>
<body>
    <header>
        <nav>
            <div class="nav-middle">
                <div class="logo">
                    <a class="nav_a" href="index.php">MayShop</a>
                </div>
                <div class="search-container">
                    <input type="search" name="query" placeholder="Buscar productos" aria-label="Buscar">
                    <button type="submit">Buscar</button>
                </div>
                <div class="user-options">
                    
                    <a href="index.php">Inicio<span></span></a>
                    <a href="products.php">Productos<span></span></a>
                    <a href="ayuda_general.php">Contacto<span></span></a>
                    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) : ?>
                        <a href="favorites_page.php"><i class="fas fa-heart"></i></a>
                        <a href="profile.php"><i class="fas fa-user-gear"></i></a>
                        <a href="ver_carrito.php"><i class="fa-solid fa-cart-shopping"></i></a>
                        <a href="logout.php"><i class="fas fa-share-from-square"></i></a>
                    <?php else : ?>
                        <a href="login.php">Iniciar sesión<span></span></a>
                        <a href="register.php">Registrarse<span></span></a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>
    <main>
        <?php if ($producto): ?>
            <div class="product-container">
                <div class="product-images">
                    <img src="<?php echo htmlspecialchars($imagenes[0]); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>" class="main-image">
                    <div class="thumbnails">
                        <?php foreach ($imagenes as $index => $imagen): ?>
                            <img src="<?php echo htmlspecialchars($imagen); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>" class="<?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="product-details">
                    <h2><?php echo htmlspecialchars($producto['nombre']); ?></h2>
                    <p class="price">$<?php echo number_format($producto['precio'], 2); ?></p>
                    <p><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                    <p><strong>Vendedor:</strong> <?php echo htmlspecialchars($producto['usuario']); ?></p>
                    <p><strong>Disponibilidad:</strong> <?php echo htmlspecialchars($producto['disponibilidad']); ?> unidades</p>
                    <div class="buy-options">
                        <?php if ($producto['disponibilidad'] > 0): ?>
                            <form action="carrito.php" method="post">
                                <input type="hidden" name="id_producto" value="<?php echo htmlspecialchars($producto['id_producto']); ?>">
                                <label for="cantidad">Cantidad:</label>
                                <input type="number" name="cantidad" value="1" min="1" max="<?php echo htmlspecialchars($producto['disponibilidad']); ?>" required>
                                <button type="submit">Comprar ahora</button>
                                <button type="submit" formaction="carrito.php">Agregar al carrito</button>
                                <br>
                            </form>
                        <?php else: ?>
                            <div class="availability">Producto agotado. No disponible para la compra.</div>
                        <?php endif; ?>
                        <form id="add-to-favorites-form" class="mt-2">
                            <input type="hidden" name="id_producto" value="<?php echo htmlspecialchars($producto['id_producto']); ?>">
                            <button type="button" id="add-to-favorites-button" class="btn <?php echo $is_favorite ? 'btn-danger' : 'btn-outline-danger'; ?>">
                                <i class="fas fa-heart"></i> <?php echo $is_favorite ? 'Producto agregado' : 'Agregar a Favoritos'; ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="reviews">
                <h3>Opiniones del producto</h3>
                <div>
                    <?php foreach ($opiniones as $opinion): ?>
                        <div class="review">
                            <p><strong><?php echo htmlspecialchars($opinion['usuario']); ?></strong></p>
                            <?php if ($opinion['imagen']): ?>
                                <img src="<?php echo htmlspecialchars($opinion['imagen']); ?>" alt="Imagen de la opinión">
                            <?php endif; ?>
                            <p><?php echo htmlspecialchars($opinion['opinion']); ?></p>
                            <p><?php echo str_repeat('★', $opinion['calificacion']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="related-products">
                <h3>Quienes vieron este producto también compraron</h3>
                <div class="products">
                    <?php foreach ($productos_relacionados as $producto_relacionado): ?>
                        <div class="product">
                            <img src="<?php echo htmlspecialchars($producto_relacionado['imagenes']); ?>" alt="<?php echo htmlspecialchars($producto_relacionado['nombre']); ?>">
                            <div>
                                <h4><?php echo htmlspecialchars($producto_relacionado['nombre']); ?></h4>
                                <p>$<?php echo number_format($producto_relacionado['precio'], 2); ?></p>
                                <a href="detalle.php?id_producto=<?php echo htmlspecialchars($producto_relacionado['id_producto']); ?>">Ver detalles</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <p>Producto no encontrado.</p>
        <?php endif; ?>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const thumbnails = document.querySelectorAll('img[data-index]');
            const mainImage = document.querySelector('.main-image');
            
            thumbnails.forEach(thumbnail => {
                thumbnail.addEventListener('click', function () {
                    const index = this.getAttribute('data-index');
                    const newSrc = this.getAttribute('src');
                    
                    mainImage.setAttribute('src', newSrc);
                    thumbnails.forEach(img => img.classList.remove('active'));
                    this.classList.add('active');
                });
            });
        });
    </script>
</body>
</html>


