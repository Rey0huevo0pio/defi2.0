<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

require 'config.php';

// Obtener el historial de pedidos del usuario
$id_usuario = $_SESSION['id_usuario'];
$sql = "SELECT p.id_pedido, p.numero_pedido, p.fecha_pedido, p.estado, p.total, d.id_producto, prod.nombre as producto, d.cantidad, d.precio, prod.imagenes 
        FROM pedidos p 
        JOIN pedido_productos d ON p.id_pedido = d.id_pedido 
        JOIN productos prod ON d.id_producto = prod.id_producto 
        WHERE p.id_usuario = ?
        ORDER BY p.fecha_pedido DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$pedidos = [];
while ($row = $result->fetch_assoc()) {
    $pedidos[$row['id_pedido']]['numero_pedido'] = $row['numero_pedido'];
    $pedidos[$row['id_pedido']]['fecha_pedido'] = $row['fecha_pedido'];
    $pedidos[$row['id_pedido']]['estado'] = $row['estado'];
    $pedidos[$row['id_pedido']]['total'] = $row['total'];
    $pedidos[$row['id_pedido']]['productos'][] = $row;
}
$stmt->close();

// Obtener las direcciones del usuario
$sql = "SELECT id_envio, direccion, ciudad, estado, codigo_postal, id_pais FROM envios WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$direcciones = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener la lista de países
$sql = "SELECT id_pais, nombre FROM paises";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$paises = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();

function getBadgeClass($estado)
{
    switch ($estado) {
        case 'procesando':
            return 'badge-warning';
        case 'enviado':
            return 'badge-info';
        case 'entregado':
            return 'badge-success';
        default:
            return 'badge-secondary';
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>MayShop - Perfil de Usuario</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./css/profile.css">

</head>

<body>

    <header>
        <nav>
            <div class="nav-middle">
                <div class="logo">
                    <a class="nav_a" href="index.php">MayShop</a>
                </div>

                <div class="user-options">

                    <a href="index.php">Inicio<span></span></a>
                    <a href="products.php">Productos<span></span></a>
                    <a href="ayuda_general.php">Contacto<span></span></a>
                    <a href="ver_carrito.php" class="fa-solid fa-cart-shopping"><span></span></a>
                    <a href="logout.php" class="fas fa-share-from-square"><span></span></a>
                </div>
            </div>
        </nav>
    </header>

   
    
<main class="container mt-4">
        <section class="profile">
            <h2>Perfil de Usuario</h2>

            <?php if (isset($_SESSION['success'])) : ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['success'];
                    unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])) : ?>
                <div class="alert alert-danger">
                    <?php echo $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-body">
                    <p class="card-text">
                        <strong>Nombre de usuario:</strong> <?php echo htmlspecialchars($_SESSION['nombre']); ?>
                        <button class="btn btn-secondary btn-sm" onclick="openModal('editProfileModal')">✎</button>
                    </p>
                    <p class="card-text">
                        <strong>Correo electrónico:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?>
                        <button class="btn btn-secondary btn-sm" onclick="openModal('editProfileModal')">✎</button>
                    </p>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="card-title">Direcciones de Envío</h3>
                    <ul class="list-group">
                        <?php foreach ($direcciones as $direccion) : ?>
                            <li class="list-group-item">
                                <p><strong>Dirección:</strong> <?php echo htmlspecialchars($direccion['direccion']); ?></p>
                                <p><strong>Ciudad:</strong> <?php echo htmlspecialchars($direccion['ciudad']); ?></p>
                                <p><strong>Estado:</strong> <?php echo htmlspecialchars($direccion['estado']); ?></p>
                                <p><strong>Código Postal:</strong> <?php echo htmlspecialchars($direccion['codigo_postal']); ?></p>
                                <p><strong>País:</strong> <?php echo htmlspecialchars($direccion['id_pais']); ?></p>
                                <button class="btn btn-primary btn-sm" onclick="openModal('editAddressModal', this)" data-id_envio="<?php echo $direccion['id_envio']; ?>" data-direccion="<?php echo htmlspecialchars($direccion['direccion']); ?>" data-ciudad="<?php echo htmlspecialchars($direccion['ciudad']); ?>" data-estado="<?php echo htmlspecialchars($direccion['estado']); ?>" data-codigo_postal="<?php echo htmlspecialchars($direccion['codigo_postal']); ?>" data-id_pais="<?php echo $direccion['id_pais']; ?>">Editar</button>
                                <form action="eliminar_direccion_perfil.php" method="post" style="display:inline;">
                                    <input type="hidden" name="id_envio" value="<?php echo $direccion['id_envio']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <button class="btn btn-success btn-sm mt-3" onclick="openModal('addAddressModal')">Agregar Dirección</button>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Historial de Pedidos</h3>
                    <ul class="list-group">
                        <?php if (count($pedidos) > 0) : ?>
                            <?php foreach ($pedidos as $id_pedido => $pedido) : ?>
                                <li class="list-group-item">
                                    <strong>Número de pedido:</strong> <?php echo htmlspecialchars($pedido['numero_pedido']); ?><br>
                                    <strong>Fecha del pedido:</strong> <?php echo htmlspecialchars($pedido['fecha_pedido']); ?><br>
                                    <strong>Estado:</strong> <span class="badge <?php echo getBadgeClass($pedido['estado']); ?>"><?php echo htmlspecialchars($pedido['estado']); ?></span><br>
                                    <strong>Total:</strong> $<?php echo number_format($pedido['total'], 2); ?>
                                    <ul class="list-group mt-2">
                                        <?php foreach ($pedido['productos'] as $producto) : ?>
                                            <li class="list-group-item">
                                                <?php
                                                $imagenes = json_decode($producto['imagenes']);
                                                if ($imagenes) : ?>
                                                    <img src="<?php echo htmlspecialchars($imagenes[0]); ?>" alt="<?php echo htmlspecialchars($producto['producto']); ?>" class="img-thumbnail" style="width: 50px; height: 50px; float: left; margin-right: 15px;">
                                                <?php endif; ?>
                                                <strong>Producto:</strong> <?php echo htmlspecialchars($producto['producto']); ?><br>
                                                <strong>Precio:</strong> $<?php echo number_format($producto['precio'], 2); ?><br>
                                                <strong>Cantidad:</strong> <?php echo htmlspecialchars($producto['cantidad']); ?><br>
                                                <button class="btn btn-primary btn-sm mt-2" onclick="openModal('opinionModal', this)" data-id_producto="<?php echo $producto['id_producto']; ?>" data-nombre_producto="<?php echo htmlspecialchars($producto['producto']); ?>">Opinar</button>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <li class="list-group-item">No has realizado ningún pedido.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </section>
    </main>

    <!-- Modal para editar perfil -->
    <div class="modal" id="editProfileModal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Perfil</h5>
                <span class="close" onclick="closeModal('editProfileModal')">&times;</span>
            </div>
            <form action="update_profile.php" method="post">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nombre">Nombre de usuario:</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($_SESSION['nombre']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Correo electrónico:</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Nueva contraseña (dejar en blanco si no se desea cambiar):</label>
                        <input type="password" class="form-control" id="password" name="password">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editProfileModal')">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para editar dirección de envío -->
    <div class="modal" id="editAddressModal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Dirección de Envío</h5>
                <span class="close" onclick="closeModal('editAddressModal')">&times;</span>
            </div>
            <form action="update_address.php" method="post">
                <div class="modal-body">
                    <input type="hidden" id="id_envio" name="id_envio">
                    <div class="form-group">
                        <label for="direccion">Dirección:</label>
                        <input type="text" class="form-control" id="direccion" name="direccion" required>
                    </div>
                    <div class="form-group">
                        <label for="ciudad">Ciudad:</label>
                        <input type="text" class="form-control" id="ciudad" name="ciudad" required>
                    </div>
                    <div class="form-group">
                        <label for="estado">Estado:</label>
                        <input type="text" class="form-control" id="estado" name="estado" required>
                    </div>
                    <div class="form-group">
                        <label for="codigo_postal">Código Postal:</label>
                        <input type="text" class="form-control" id="codigo_postal" name="codigo_postal" required>
                    </div>
                    <div class="form-group">
                        <label for="id_pais">País:</label>
                        <select class="form-control" id="id_pais" name="id_pais" required>
                            <?php foreach ($paises as $pais) : ?>
                                <option value="<?php echo $pais['id_pais']; ?>"><?php echo htmlspecialchars($pais['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editAddressModal')">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para agregar nueva dirección -->
    <div class="modal" id="addAddressModal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Nueva Dirección</h5>
                <span class="close" onclick="closeModal('addAddressModal')">&times;</span>
            </div>
            <form action="agregar_direccion_perfil.php" method="post">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nueva_direccion">Dirección:</label>
                        <input type="text" class="form-control" id="nueva_direccion" name="nueva_direccion" required>
                    </div>
                    <div class="form-group">
                        <label for="nueva_ciudad">Ciudad:</label>
                        <input type="text" class="form-control" id="nueva_ciudad" name="nueva_ciudad" required>
                    </div>
                    <div class="form-group">
                        <label for="nueva_estado">Estado:</label>
                        <input type="text" class="form-control" id="nueva_estado" name="nueva_estado" required>
                    </div>
                    <div class="form-group">
                        <label for="nuevo_codigo_postal">Código Postal:</label>
                        <input type="text" class="form-control" id="nuevo_codigo_postal" name="nuevo_codigo_postal" required>
                    </div>
                    <div class="form-group">
                        <label for="nuevo_id_pais">País:</label>
                        <select class="form-control" id="nuevo_id_pais" name="nuevo_id_pais" required>
                            <?php foreach ($paises as $pais) : ?>
                                <option value="<?php echo $pais['id_pais']; ?>"><?php echo htmlspecialchars($pais['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addAddressModal')">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar Dirección</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para opinar sobre un producto -->
    <div class="modal" id="opinionModal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Opinar sobre el producto</h5>
                <span class="close" onclick="closeModal('opinionModal')">&times;</span>
            </div>
            <form action="guardar_opinion.php" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="id_producto" name="id_producto">
                    <div class="form-group">
                        <label for="nombre_producto">Producto:</label>
                        <input type="text" class="form-control" id="nombre_producto" name="nombre_producto" readonly>
                    </div>
                    <div class="form-group">
                        <label for="calificacion">Calificación:</label>
                        <select class="form-control" id="calificacion" name="calificacion" required>
                            <option value="1">1 estrella</option>
                            <option value="2">2 estrellas</option>
                            <option value="3">3 estrellas</option>
                            <option value="4">4 estrellas</option>
                            <option value="5">5 estrellas</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="opinion">Opinión:</label>
                        <textarea class="form-control" id="opinion" name="opinion" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="imagen">Subir imagen (opcional):</label>
                        <input type="file" class="form-control-file" id="imagen" name="imagen">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('opinionModal')">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar opinión</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id, button) {
            var modal = document.getElementById(id);
            modal.style.display = 'flex';

            if (id === 'editAddressModal' && button) {
                var idEnvio = button.getAttribute('data-id_envio');
                var direccion = button.getAttribute('data-direccion');
                var ciudad = button.getAttribute('data-ciudad');
                var estado = button.getAttribute('data-estado');
                var codigoPostal = button.getAttribute('data-codigo_postal');
                var idPais = button.getAttribute('data-id_pais');
                document.getElementById('id_envio').value = idEnvio;
                document.getElementById('direccion').value = direccion;
                document.getElementById('ciudad').value = ciudad;
                document.getElementById('estado').value = estado;
                document.getElementById('codigo_postal').value = codigoPostal;
                document.getElementById('id_pais').value = idPais;
            }

            if (id === 'opinionModal' && button) {
                var idProducto = button.getAttribute('data-id_producto');
                var nombreProducto = button.getAttribute('data-nombre_producto');
                document.getElementById('id_producto').value = idProducto;
                document.getElementById('nombre_producto').value = nombreProducto;
            }
        }

        function closeModal(id) {
            var modal = document.getElementById(id);
            modal.style.display = 'none';
        }

        document.querySelectorAll('[data-toggle="modal"]').forEach(function(button) {
            button.addEventListener('click', function() {
                var targetId = this.getAttribute('data-target').substring(1);
                openModal(targetId, this);
            });
        });

        document.querySelectorAll('.close').forEach(function(span) {
            span.addEventListener('click', function() {
                closeModal(this.closest('.modal').id);
            });
        });

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                closeModal(event.target.id);
            }
        }
    </script>
</body>

</html>
