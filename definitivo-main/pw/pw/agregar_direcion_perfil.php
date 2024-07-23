<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_usuario = $_SESSION['id_usuario'];
    $direccion = $_POST['nueva_direccion'];
    $ciudad = $_POST['nueva_ciudad'];
    $estado = $_POST['nueva_estado'];
    $codigo_postal = $_POST['nuevo_codigo_postal'];
    $id_pais = $_POST['nuevo_id_pais'];

    // Validar y sanitizar las entradas
    $direccion = htmlspecialchars(strip_tags($direccion));
    $ciudad = htmlspecialchars(strip_tags($ciudad));
    $estado = htmlspecialchars(strip_tags($estado));
    $codigo_postal = htmlspecialchars(strip_tags($codigo_postal));
    $id_pais = (int)$id_pais;

    // Insertar la nueva dirección
    $sql = "INSERT INTO envios (id_usuario, direccion, ciudad, estado, codigo_postal, id_pais) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssi", $id_usuario, $direccion, $ciudad, $estado, $codigo_postal, $id_pais);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Dirección agregada con éxito.";
    } else {
        $_SESSION['error'] = "Hubo un problema al agregar la dirección.";
    }
    $stmt->close();
    $conn->close();
    header("Location: profile.php");
    exit();
}
?>
