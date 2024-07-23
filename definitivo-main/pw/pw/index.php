<?php
session_start();
require 'config.php';
// Obtener todas las categorías de la base de datos
$stmt = $conn->prepare("SELECT id_categoria, nombre FROM categorias");
$stmt->execute();
$result = $stmt->get_result();
$categorias = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Verificar si se ha seleccionado una categoría
$categoria_seleccionada = isset($_GET['categoria']) ? $_GET['categoria'] : null;

if ($categoria_seleccionada) {
    // Obtener los productos de la categoría seleccionada
    $stmt = $conn->prepare("SELECT p.id_producto, p.nombre, p.descripcion, p.precio, p.imagenes, p.disponibilidad, u.nombre as usuario 
                            FROM productos p 
                            JOIN Usuarios u ON p.id_usuario = u.id_usuario
                            WHERE p.categoria_id = ?");
    $stmt->bind_param("i", $categoria_seleccionada);
} else {
    // Obtener todos los productos de la base de datos
    $stmt = $conn->prepare("SELECT p.id_producto, p.nombre, p.descripcion, p.precio, p.imagenes, p.disponibilidad, u.nombre as usuario 
                            FROM productos p 
                            JOIN Usuarios u ON p.id_usuario = u.id_usuario");
}
$stmt->execute();
$result = $stmt->get_result();
$productos = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>MayShop - Página de Inicio</title>
    <link rel="stylesheet" href="./css/index.css">
    <link rel="icon" href="./banners/logo.webp" type="image/x-icon">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
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
                    <div class="dropdown" >
                        <a href="javascript:void(0)" class="dropbtn" >Categorías<span></span></a>
                        <div class="dropdown-content">
                            <ul>
                                <?php foreach ($categorias as $categoria) : ?>
                                    <li><a href="?categoria=<?php echo $categoria['id_categoria']; ?>"><?php echo htmlspecialchars($categoria['nombre']); ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
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

    <div class="bo_ti">
        <div class="BC_may">
            <h2>Bienvenido a </h2>
            <h2 class="bo_ti_h2">MayShop</h2>
        </div>
        <?php if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) : ?>
            <p><a class="CM_may" href="register.php">Comienza ahora</a></p>
        <?php endif; ?>
    </div>

    <section id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
        <ol class="carousel-indicators">
            <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
            <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
            <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
        </ol>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img class="d-block w-100" src="./images/promo1.webp" alt="Laptop">
                <div class="carousel-caption d-none d-md-block">
                    <h5>Modern Laptop</h5>
                </div>
            </div>
            <div class="carousel-item">
                <img class="d-block w-100" src="./images/promo2.webp" alt="Producto 2">
                <div class="carousel-caption d-none d-md-block">
                    <h5>Producto 2</h5>
                </div>
            </div>
            <div class="carousel-item">
                <img class="d-block w-100" src="./images/promo3.webp" alt="Producto 3">
                <div class="carousel-caption d-none d-md-block">
                    <h5>Producto 3</h5>
                </div>
            </div>
        </div>
        <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Anterior</span>
        </a>
        <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Siguiente</span>
        </a>
    </section>



    <main class="main-container">

        <div class="container">

            <section>



                <h2 class="Product_h2">Productos</h2>
                <div class="product-grid">
                    <?php foreach ($productos as $producto) : ?>
                        <div class="product">
                            <a href="detalle_producto.php?id_producto=<?php echo $producto['id_producto']; ?>">
                                <div class="product-content">
                                    <?php
                                    $imagenes = json_decode($producto['imagenes']);
                                    if ($imagenes) : ?>
                                        <img src="<?php echo htmlspecialchars($imagenes[0]); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                                    <?php endif; ?>
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
            </section>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var dropdown = document.querySelector('.dropdown');
            var dropdownContent = document.querySelector('.dropdown-content');

            function openDropdown() {
                dropdown.classList.add('open');
                setTimeout(function() {
                    dropdownContent.style.opacity = '1';
                }, 50);
            }

            function closeDropdown() {
                dropdownContent.style.opacity = '0';
                setTimeout(function() {
                    dropdown.classList.remove('open');
                }, 500);
            }

            dropdown.addEventListener('click', function(event) {
                event.stopPropagation();
                if (dropdown.classList.contains('open')) {
                    closeDropdown();
                } else {
                    openDropdown();
                }
            });

            document.addEventListener('click', function(event) {
                if (!dropdown.contains(event.target)) {
                    closeDropdown();
                }
            });

            dropdownContent.addEventListener('click', function(event) {
                event.stopPropagation(); // Evita que el menú se cierre al hacer clic en él
            });
        });
    </script>





</body>

</html>