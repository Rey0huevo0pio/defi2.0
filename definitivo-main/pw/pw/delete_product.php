<?php
session_start();
require 'config.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

// Verificar si se ha enviado un ID de producto
if (isset($_POST['id_producto'])) {
    $id_producto = $_POST['id_producto'];

    // Obtener la ruta de las imágenes del producto antes de eliminarlo
    $stmt = $conn->prepare("SELECT imagenes FROM productos WHERE id_producto = ? AND id_usuario = ?");
    $stmt->bind_param("ii", $id_producto, $_SESSION['id_usuario']);
    $stmt->execute();
    $stmt->bind_result($ruta_imagenes_json);
    $stmt->fetch();
    $stmt->close();

    // Decodificar la ruta de las imágenes
    $imagenes = json_decode($ruta_imagenes_json, true);
    // Obtener la carpeta donde están almacenadas las imágenes
    if (!empty($imagenes)) {
        $carpeta_imagenes = dirname($imagenes[0]);
    } else {
        $carpeta_imagenes = null;
    }

    // Eliminar las referencias en la tabla favoritos
    $stmt = $conn->prepare("DELETE FROM favoritos WHERE id_producto = ? AND id_usuario = ?");
    $stmt->bind_param("ii", $id_producto, $_SESSION['id_usuario']);
    $stmt->execute();
    $stmt->close();

    // Eliminar las referencias en la tabla pedido_productos
    $stmt = $conn->prepare("DELETE FROM pedido_productos WHERE id_producto = ?");
    $stmt->bind_param("i", $id_producto);
    $stmt->execute();
    $stmt->close();

    // Eliminar el producto de la base de datos
    $stmt = $conn->prepare("DELETE FROM productos WHERE id_producto = ? AND id_usuario = ?");
    $stmt->bind_param("ii", $id_producto, $_SESSION['id_usuario']);
    if ($stmt->execute()) {
        // Función para eliminar la carpeta y su contenido
        function eliminarCarpeta($carpeta) {
            if (is_dir($carpeta)) {
                $objetos = scandir($carpeta);
                foreach ($objetos as $objeto) {
                    if ($objeto != "." && $objeto != "..") {
                        if (is_dir($carpeta . "/" . $objeto))
                            eliminarCarpeta($carpeta . "/" . $objeto);
                        else
                            unlink($carpeta . "/" . $objeto);
                    }
                }
                rmdir($carpeta);
            }
        }

        // Eliminar la carpeta de imágenes del sistema de archivos si existe
        if ($carpeta_imagenes && is_dir($carpeta_imagenes)) {
            eliminarCarpeta($carpeta_imagenes);
        }
        $_SESSION['success'] = "Producto eliminado correctamente.";
    } else {
        $_SESSION['error'] = "Hubo un error al eliminar el producto.";
    }
    $stmt->close();
} else {
    $_SESSION['error'] = "ID de producto no proporcionado.";
}

$conn->close();
header("Location: products.php");
exit;
?>
