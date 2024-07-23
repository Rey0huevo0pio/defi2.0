<?php
session_start();
require 'config.php';

$response = array('status' => 'error', 'message' => 'Ocurrió un error inesperado.');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $response['message'] = 'No autorizado.';
    echo json_encode($response);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

if (isset($_POST['nueva_direccion_input']) && isset($_POST['nueva_ciudad_input']) && isset($_POST['nueva_estado_input']) && isset($_POST['nueva_codigo_postal_input']) && isset($_POST['nueva_pais_input'])) {
    $nueva_direccion = $_POST['nueva_direccion_input'];
    $nueva_ciudad = $_POST['nueva_ciudad_input'];
    $nueva_estado = $_POST['nueva_estado_input'];
    $nueva_codigo_postal = $_POST['nueva_codigo_postal_input'];
    $id_pais = $_POST['nueva_pais_input'];

    $stmt = $conn->prepare("INSERT INTO envios (id_usuario, direccion, ciudad, estado, codigo_postal, id_pais) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssi", $id_usuario, $nueva_direccion, $nueva_ciudad, $nueva_estado, $nueva_codigo_postal, $id_pais);

    if ($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = 'Dirección guardada con éxito.';
        $response['id_envio'] = $stmt->insert_id;
        $response['direccion'] = $nueva_direccion;
        $response['ciudad'] = $nueva_ciudad;
        $response['estado'] = $nueva_estado;
        $response['codigo_postal'] = $nueva_codigo_postal;
        $response['id_pais'] = $id_pais;
    } else {
        $response['message'] = 'Error al guardar la dirección: ' . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>

