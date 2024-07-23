<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

require 'config.php';

$id_envio = $_POST['id_envio'];
$direccion = $_POST['direccion'];
$ciudad = $_POST['ciudad'];
$estado = $_POST['estado'];
$codigo_postal = $_POST['codigo_postal'];
$id_pais = $_POST['id_pais'];

$sql = "UPDATE envios SET direccion = ?, ciudad = ?, estado = ?, codigo_postal = ?, id_pais = ? WHERE id_envio = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssii", $direccion, $ciudad, $estado, $codigo_postal, $id_pais, $id_envio);

if ($stmt->execute()) {
    $_SESSION['success'] = "Dirección actualizada con éxito.";
} else {
    $_SESSION['error'] = "Hubo un problema al actualizar la dirección.";
}

$stmt->close();
$conn->close();

header("Location: profile.php");
exit();
?>
