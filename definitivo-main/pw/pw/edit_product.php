<?php
session_start();
require 'config.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

// Verificar si se ha enviado un ID de producto
if (!isset($_GET['id_producto'])) {
    header("location: products.php");
    exit;
}

$id_producto = $_GET['id_producto'];

// Obtener los datos del producto
$stmt = $conn->prepare("SELECT nombre, descripcion, precio, disponibilidad, categoria_id FROM productos WHERE id_producto = ? AND id_usuario = ?");
$stmt->bind_param("ii", $id_producto, $_SESSION['id_usuario']);
$stmt->execute();
$result = $stmt->get_result();
$producto = $result->fetch_assoc();
$stmt->close();

// Obtener todas las categorías de la base de datos
$stmt = $conn->prepare("SELECT id_categoria, nombre FROM categorias");
$stmt->execute();
$result = $stmt->get_result();
$categorias = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Manejar el envío del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $disponibilidad = $_POST['disponibilidad'];
    $categoria_id = $_POST['categoria_id'];

    // Actualizar el producto en la base de datos sin cambiar la imagen
    $stmt = $conn->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, disponibilidad = ?, categoria_id = ? WHERE id_producto = ? AND id_usuario = ?");
    $stmt->bind_param("ssdisii", $nombre, $descripcion, $precio, $disponibilidad, $categoria_id, $id_producto, $_SESSION['id_usuario']);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Producto actualizado correctamente.";
        header("Location: products.php");
        exit;
    } else {
        echo "Hubo un error al actualizar el producto.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>MayShop - Editar Producto</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <header class="bg-primary text-white p-3">
        <div class="container d-flex justify-content-between align-items-center">
            <h1 class="logo mb-0">MayShop</h1>
            <nav>
                <ul class="nav">
                    <li class="nav-item"><a class="nav-link text-white" href="index.php">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="products.php">Productos</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="contact.php">Contacto</a></li>
                    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                        <li class="nav-item"><a class="nav-link text-white" href="profile.php">Perfil</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="logout.php">Cerrar sesión</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link text-white" href="login.php">Iniciar sesión</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="register.php">Registrarse</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container mt-4">
        <h2>Editar Producto</h2>
        <form action="edit_product.php?id_producto=<?php echo $id_producto; ?>" method="post">
            <div class="form-group">
                <label for="nombre">Nombre del Producto</label>
                <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="precio">Precio</label>
                <input type="number" class="form-control" id="precio" name="precio" step="0.01" value="<?php echo htmlspecialchars($producto['precio']); ?>" required>
            </div>
            <div class="form-group">
                <label for="disponibilidad">Disponibilidad</label>
                <input type="number" class="form-control" id="disponibilidad" name="disponibilidad" value="<?php echo htmlspecialchars($producto['disponibilidad']); ?>" required>
            </div>
            <div class="form-group">
                <label for="categoria_id">Categoría</label>
                <select class="form-control" id="categoria_id" name="categoria_id" required>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?php echo htmlspecialchars($categoria['id_categoria']); ?>" <?php if ($categoria['id_categoria'] == $producto['categoria_id']) echo 'selected'; ?>><?php echo htmlspecialchars($categoria['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </form>
    </main>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
