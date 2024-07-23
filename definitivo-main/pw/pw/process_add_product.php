<?php
session_start();
require 'config.php';

// Verificar que se hayan enviado todos los datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $disponibilidad = $_POST['disponibilidad'];
    $categoria_id = $_POST['categoria_id'];
    $id_usuario = $_SESSION['id_usuario'];

    // Crear la carpeta del usuario si no existe
    $user_dir = "images/user_$id_usuario";
    if (!is_dir($user_dir)) {
        mkdir($user_dir, 0777, true);
    }

    // Manejo de las imágenes del producto
    $image_paths = [];
    foreach ($_FILES['imagenes']['tmp_name'] as $key => $tmp_name) {
        $file_name = uniqid() . '.' . strtolower(pathinfo($_FILES["imagenes"]["name"][$key], PATHINFO_EXTENSION));
        $target_file = $user_dir . '/' . $file_name;

        $check = getimagesize($tmp_name);
        if ($check !== false) {
            if (move_uploaded_file($tmp_name, $target_file)) {
                $image_paths[] = $target_file;
            } else {
                echo "Lo siento, hubo un error al subir tu archivo.";
                exit;
            }
        } else {
            echo "El archivo no es una imagen.";
            exit;
        }
    }

    // Convertir array de rutas de imágenes a JSON
    $imagenes_json = json_encode($image_paths);

    // Insertar el nuevo producto en la base de datos
    $stmt = $conn->prepare("INSERT INTO productos (nombre, descripcion, precio, disponibilidad, categoria_id, id_usuario, imagenes) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdiiss", $nombre, $descripcion, $precio, $disponibilidad, $categoria_id, $id_usuario, $imagenes_json);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Producto agregado correctamente.";
        header("Location: products.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
$conn->close();
?>

