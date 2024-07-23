<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = $_SESSION['id_usuario'];
    $pedido = $_POST['pedido'];
    $motivo = $_POST['motivo'];

    // Crear una carpeta única para esta solicitud
    $uniqueDir = 'fotos_reembolsos/' . uniqid('solicitud_', true);
    if (!mkdir($uniqueDir, 0777, true)) {
        $_SESSION['error'] = "Error al crear el directorio para las fotos.";
        header('Location: ayuda_general.php');
        exit;
    }

    // Manejo de las fotos del producto
    $photos = $_FILES['fotos'];
    $uploadedFiles = [];

    if (!empty($photos['name'][0])) {
        for ($i = 0; $i < count($photos['name']); $i++) {
            $extension = pathinfo($photos['name'][$i], PATHINFO_EXTENSION);
            $newFileName = uniqid('img_', true) . '.' . $extension;
            $targetFilePath = $uniqueDir . '/' . $newFileName;

            if (move_uploaded_file($photos['tmp_name'][$i], $targetFilePath)) {
                $uploadedFiles[] = $targetFilePath;
            } else {
                $_SESSION['error'] = "Error al subir el archivo: " . $photos['name'][$i];
                header('Location: ayuda_general.php');
                exit;
            }
        }
    }

    $photosString = implode(', ', $uploadedFiles);

    // Verificar si la preparación de la consulta es exitosa
    $stmt = $conn->prepare("INSERT INTO solicitudes_reembolso (id_pedido, id_usuario, motivo, ruta_fotos, estado) VALUES (?, ?, ?, ?, 'pendiente')");
    if ($stmt === false) {
        $_SESSION['error'] = 'Error en la consulta SQL: ' . htmlspecialchars($conn->error);
        header('Location: ayuda_general.php');
        exit;
    }
    $stmt->bind_param("iiss", $pedido, $id_usuario, $motivo, $photosString);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Solicitud de reembolso enviada correctamente.";
    } else {
        $_SESSION['error'] = "Error al enviar la solicitud: " . htmlspecialchars($stmt->error);
    }

    $stmt->close();
    $conn->close();
    header('Location: ayuda_general.php');
    exit;
} else {
    $_SESSION['error'] = "Método de solicitud no válido.";
    header('Location: ayuda_general.php');
    exit;
}
?>
