<?php
session_start();
require 'config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

// Obtener pedidos del usuario
$stmt = $conn->prepare("SELECT p.id_pedido, p.numero_pedido FROM pedidos p WHERE p.id_usuario = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$pedidos = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>MayShop - Ayuda</title>
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
                        <li class="nav-item"><a class="nav-link text-white" href="products.php">Productos</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="contact.php">Contacto</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="ver_carrito.php"><i class="fa-solid fa-cart-shopping" style="font-size: 20px;"></i></a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="logout.php"><i class="fas fa-share-from-square" style="font-size: 20px;"></i></a></li>
                    </ul>
                </div>
            </nav>
        </div>
    </header>

    <main class="container mt-4">
        <h2>Ayuda</h2>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>
        <p>Seleccione el tipo de ayuda que necesita:</p>
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="devolucion-tab" data-toggle="tab" href="#devolucion" role="tab" aria-controls="devolucion" aria-selected="true">Devolución</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="reembolso-tab" data-toggle="tab" href="#reembolso" role="tab" aria-controls="reembolso" aria-selected="false">Reembolso</a>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="devolucion" role="tabpanel" aria-labelledby="devolucion-tab">
                <h3 class="mt-3">Solicitud de Devolución</h3>
                <form action="solicitud_devolucion.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="pedido">Número de Pedido</label>
                        <select class="form-control" id="pedido" name="pedido" required>
                            <option value="">Seleccione su pedido</option>
                            <?php foreach ($pedidos as $pedido): ?>
                                <option value="<?php echo $pedido['id_pedido']; ?>">Pedido #<?php echo $pedido['numero_pedido']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="motivo">Motivo</label>
                        <textarea class="form-control" id="motivo" name="motivo" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="fotos">Fotos del Producto</label>
                        <input type="file" class="form-control-file" id="fotos" name="fotos[]" multiple>
                    </div>
                    <button type="submit" class="btn btn-primary">Enviar Solicitud</button>
                </form>
            </div>
            <div class="tab-pane fade" id="reembolso" role="tabpanel" aria-labelledby="reembolso-tab">
                <h3 class="mt-3">Solicitud de Reembolso</h3>
                <form action="solicitud_reembolso.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="pedido">Número de Pedido</label>
                        <select class="form-control" id="pedido" name="pedido" required>
                            <option value="">Seleccione su pedido</option>
                            <?php foreach ($pedidos as $pedido): ?>
                                <option value="<?php echo $pedido['id_pedido']; ?>">Pedido #<?php echo $pedido['numero_pedido']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="motivo">Motivo</label>
                        <textarea class="form-control" id="motivo" name="motivo" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="fotos">Fotos del Producto</label>
                        <input type="file" class="form-control-file" id="fotos" name="fotos[]" multiple>
                    </div>
                    <button type="submit" class="btn btn-primary">Enviar Solicitud</button>
                </form>
            </div>
        </div>
    </main>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
