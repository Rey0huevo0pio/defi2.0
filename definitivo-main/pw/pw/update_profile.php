<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_usuario = $_SESSION['id_usuario'];
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validar y sanitizar las entradas
    $nombre = htmlspecialchars(strip_tags($nombre));
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    if (!empty($password)) {
        // Hash de la nueva contraseña
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
    }

    // Actualizar el perfil del usuario
    if (!empty($password)) {
        $sql = "UPDATE usuarios SET nombre = ?, email = ?, contraseña = ? WHERE id_usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $nombre, $email, $password_hashed, $id_usuario);
    } else {
        $sql = "UPDATE usuarios SET nombre = ?, email = ? WHERE id_usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $nombre, $email, $id_usuario);
    }

    if ($stmt->execute()) {
        $_SESSION['nombre'] = $nombre;
        $_SESSION['email'] = $email;
        $_SESSION['success'] = "Perfil actualizado con éxito.";
    } else {
        $_SESSION['error'] = "Hubo un problema al actualizar el perfil.";
    }

    $stmt->close();
    $conn->close();
    header("Location: profile.php");
    exit();
}
?>
