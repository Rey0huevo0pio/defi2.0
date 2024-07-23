<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_envio = $_POST['id_envio'];
    $id_usuario = $_SESSION['id_usuario'];

    // Eliminar la dirección
    $sql = "DELETE FROM envios WHERE id_envio = ? AND id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_envio, $id_usuario);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Dirección eliminada con éxito.";
    } else {
        $_SESSION['error'] = "Hubo un problema al eliminar la dirección.";
    }
    $stmt->close();
    $conn->close();
    header("Location: profile.php");
    exit();
}
?>
