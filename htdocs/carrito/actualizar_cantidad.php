<?php
function actualizarCarrito($codigo, $cantidad)
{
    // Comprobar si la cantidad es válida
    if ($cantidad <= 0) {
        return false; // No permitir cantidades negativas o cero
    }

    if (isset($_SESSION['carrito'])) {
        foreach ($_SESSION['carrito'] as $key => $articulo) {
            if ($articulo['codigo'] == $codigo) {
                // Actualiza la cantidad
                $_SESSION['carrito'][$key]['cantidad'] = $cantidad;
                return true;
            }
        }
    }
    return false; // Si no se encontró el artículo, devolver false
}

// Verificar si se ha enviado una actualización
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['codigo']) && isset($_POST['cantidad'])) {
    $codigo = $_POST['codigo'];
    $cantidad = (int) $_POST['cantidad']; // Asegurarse de que la cantidad es un número entero

    // Llamada a la función para actualizar el carrito
    $actualizado = actualizarCarrito($codigo, $cantidad);

    // Si se ha actualizado correctamente, redirigir
    if ($actualizado) {
        header('Location: carrito.php');
        exit;
    } else {
        // Puedes manejar el error, como mostrar un mensaje
        echo "Error: cantidad no válida o artículo no encontrado.";
    }
}
?>
