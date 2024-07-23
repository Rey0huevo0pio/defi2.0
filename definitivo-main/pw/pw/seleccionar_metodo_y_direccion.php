<?php
session_start();
require 'config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

// Obtener las direcciones del usuario
$stmt = $conn->prepare("SELECT id_envio, direccion, ciudad, estado, codigo_postal, id_pais FROM envios WHERE id_usuario = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$direcciones = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener los métodos de pago
$stmt = $conn->prepare("SELECT id_metodo, nombre FROM metodos_pago");
$stmt->execute();
$result = $stmt->get_result();
$metodos_pago = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener la lista de países
$stmt = $conn->prepare("SELECT id_pais, nombre FROM paises");
$stmt->execute();
$result = $stmt->get_result();
$paises = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>MayShop - Seleccionar Método y Dirección</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./css/seleccionar_metodo_y_direccion.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-light">
                <a class="navbar-brand" href="index.php">MayShop</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
                        <li class="nav-item"><a class="nav-link" href="products.php">Mis Productos</a></li>
                        <li class="nav-item"><a class="nav-link" href="contact.php">Contacto</a></li>
                        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                            <li class="nav-item"><a class="nav-link" href="favorites_page.php"><i class="fas fa-heart"></i></a></li>
                            <li class="nav-item"><a class="nav-link" href="profile.php"><i class="fas fa-user-gear"></i></a></li>
                            <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-share-from-square"></i></a></li>
                        <?php else: ?>
                            <li class="nav-item"><a class="nav-link" href="login.php">Iniciar sesión</a></li>
                            <li class="nav-item"><a class="nav-link" href="register.php">Registrarse</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>
        </div>
    </header>

    <main class="container mt-4">
        <h2 class="mb-4">Seleccionar Método de Pago y Dirección de Envío</h2>
        <form action="confirmar_compra.php" method="post">
            <div class="form-section">
                <h5>Método de Pago</h5>
                <div class="row">
                    <?php foreach ($metodos_pago as $metodo): ?>
                        <div class="col-md-6">
                            <div class="form-check custom-card">
                                <input class="form-check-input" type="radio" name="metodo_pago" id="metodo_<?php echo $metodo['id_metodo']; ?>" value="<?php echo $metodo['id_metodo']; ?>" required>
                                <label class="form-check-label" for="metodo_<?php echo $metodo['id_metodo']; ?>">
                                    <h6><?php echo htmlspecialchars($metodo['nombre']); ?></h6>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-section">
                <h5>Dirección de Envío</h5>
                <div class="row" id="direccion_envio_list">
                    <?php if (empty($direcciones)): ?>
                        <div class="col-md-12">
                            <div class="alert alert-warning">No tienes direcciones guardadas. Por favor, agrega una nueva dirección.</div>
                        </div>
                    <?php endif; ?>
                    <?php foreach ($direcciones as $direccion): ?>
                        <div class="col-md-6">
                            <div class="form-check custom-card">
                                <input class="form-check-input" type="radio" name="direccion_envio" id="direccion_<?php echo $direccion['id_envio']; ?>" value="<?php echo $direccion['id_envio']; ?>" required>
                                <label class="form-check-label" for="direccion_<?php echo $direccion['id_envio']; ?>">
                                    <h6><?php echo htmlspecialchars($direccion['direccion']); ?></h6>
                                    <p><?php echo htmlspecialchars($direccion['ciudad'] . ", " . $direccion['estado'] . ", " . $direccion['codigo_postal'] . ", " . $direccion['id_pais']); ?></p>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="col-md-6">
                        <div class="form-check custom-card">
                            <input class="form-check-input" type="radio" name="direccion_envio" id="nueva_direccion" value="nueva">
                            <label class="form-check-label" for="nueva_direccion">
                                <h6>Agregar nueva dirección</h6>
                            </label>
                        </div>
                    </div>
                </div>

                <div id="nueva_direccion_form" style="display: none; margin-top: 20px;">
                    <div class="form-group">
                        <label for="nueva_direccion_input">Dirección</label>
                        <input type="text" class="form-control" id="nueva_direccion_input" name="nueva_direccion_input">
                    </div>
                    <div class="form-group">
                        <label for="nueva_ciudad_input">Ciudad</label>
                        <input type="text" class="form-control" id="nueva_ciudad_input" name="nueva_ciudad_input">
                    </div>
                    <div class="form-group">
                        <label for="nueva_estado_input">Estado</label>
                        <input type="text" class="form-control" id="nueva_estado_input" name="nueva_estado_input">
                    </div>
                    <div class="form-group">
                        <label for="nueva_codigo_postal_input">Código Postal</label>
                        <input type="text" class="form-control" id="nueva_codigo_postal_input" name="nueva_codigo_postal_input">
                    </div>
                    <div class="form-group">
                        <label for="nueva_pais_input">País</label>
                        <select class="form-control" id="nueva_pais_input" name="nueva_pais_input">
                            <?php foreach ($paises as $pais): ?>
                                <option value="<?php echo $pais['id_pais']; ?>"><?php echo htmlspecialchars($pais['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="button" class="btn btn-success" id="guardar_direccion_btn">Guardar Dirección</button>
                </div>
            </div>

            <div class="form-section">
                <h5>Código de Cupón</h5>
                <input type="text" name="codigo_cupon" placeholder="Código de cupón" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary mt-3">Continuar</button>
        </form>
    </main>

    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script>
        document.getElementById('nueva_direccion').addEventListener('change', function() {
            document.getElementById('nueva_direccion_form').style.display = 'block';
        });

        document.querySelectorAll('input[name="direccion_envio"]').forEach(function(element) {
            if (element.id !== 'nueva_direccion') {
                element.addEventListener('change', function() {
                    document.getElementById('nueva_direccion_form').style.display = 'none';
                });
            }
        });

        document.getElementById('guardar_direccion_btn').addEventListener('click', function() {
            var direccion = document.getElementById('nueva_direccion_input').value;
            var ciudad = document.getElementById('nueva_ciudad_input').value;
            var estado = document.getElementById('nueva_estado_input').value;
            var codigo_postal = document.getElementById('nueva_codigo_postal_input').value;
            var id_pais = document.getElementById('nueva_pais_input').value;

            $.ajax({
                url: 'guardar_direccion.php',
                method: 'POST',
                data: {
                    nueva_direccion_input: direccion,
                    nueva_ciudad_input: ciudad,
                    nueva_estado_input: estado,
                    nueva_codigo_postal_input: codigo_postal,
                    nueva_pais_input: id_pais
                },
                success: function(response) {
                    var data = JSON.parse(response);
                    if (data.status === 'success') {
                        alert('Dirección guardada con éxito.');
                        var newOption = $('<div class="col-md-6"><div class="form-check custom-card"><input class="form-check-input" type="radio" name="direccion_envio" id="direccion_' + data.id_envio + '" value="' + data.id_envio + '" checked><label class="form-check-label" for="direccion_' + data.id_envio + '"><h6>' + data.direccion + '</h6><p>' + data.ciudad + ", " + data.estado + ", " + data.codigo_postal + ", " + data.id_pais + '</p></label></div></div>');
                        $('#direccion_envio_list').append(newOption);
                        document.getElementById('nueva_direccion_form').reset();
                        document.getElementById('nueva_direccion_form').style.display = 'none';
                        document.getElementById('nueva_direccion').checked = false;
                        $('#direccion_' + data.id_envio).prop('checked', true);
                    } else {
                        alert(data.message);
                    }
                }
            });
        });
    </script>
</body>
</html>



