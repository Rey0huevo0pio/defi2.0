<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = $_SESSION['id_usuario'];
    $id_producto = $_POST['id_producto'];
    $calificacion = $_POST['calificacion'];
    $opinion = $_POST['opinion'];
    $imagen = NULL;

    // Procesar la imagen si se ha subido
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $imagen_tmp = $_FILES['imagen']['tmp_name'];
        $imagen_nombre = basename($_FILES['imagen']['name']);
        $imagen_destino = './opiniones/' . $imagen_nombre;
        if (move_uploaded_file($imagen_tmp, $imagen_destino)) {
            $imagen = $imagen_destino;
        }
    }

    $sql = "INSERT INTO opiniones (id_producto, id_usuario, calificacion, opinion, imagen) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiss", $id_producto, $id_usuario, $calificacion, $opinion, $imagen);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Opinión guardada con éxito.";
    } else {
        $_SESSION['error'] = "Error al guardar la opinión.";
    }
    
    $stmt->close();
    $conn->close();
    header("location: profile.php");
    exit;
}
?>
