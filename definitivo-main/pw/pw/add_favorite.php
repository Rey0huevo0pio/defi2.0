<?php
session_start();
require 'config.php';

$response = array();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_POST['id_producto'])) {
    $userId = $_SESSION['id_usuario'];
    $productId = $_POST['id_producto'];

    // Verificar si el producto ya está en favoritos
    $sql = "SELECT * FROM favoritos WHERE id_usuario = ? AND id_producto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // Si no está en favoritos, agregarlo
        $sql = "INSERT INTO favoritos (id_usuario, id_producto) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $productId);

        if ($stmt->execute()) {
            $response['status'] = 'success';
            $response['message'] = 'Producto agregado a favoritos';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Error al agregar el producto a favoritos';
        }
    } else {
        // Si ya está en favoritos, eliminarlo
        $sql = "DELETE FROM favoritos WHERE id_usuario = ? AND id_producto = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $productId);

        if ($stmt->execute()) {
            $response['status'] = 'info';
            $response['message'] = 'Producto eliminado de favoritos';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Error al eliminar el producto de favoritos';
        }
    }

    $stmt->close();
    $conn->close();
} else {
    $response['status'] = 'error';
    $response['message'] = 'No ha iniciado sesión';
}

echo json_encode($response);
?>

