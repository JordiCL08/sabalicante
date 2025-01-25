<?php
function actualizarCarrito($codigo, $cantidad)
{
    // Comprobar si la cantidad es válida
    if ($cantidad <= 0) {
        return false; 
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
    return false; 
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['codigo']) && isset($_POST['cantidad'])) {
    $codigo = $_POST['codigo'];
    $cantidad = (int) $_POST['cantidad']; 
    //Llamada a la función para actualizar el carrito
    $actualizado = actualizarCarrito($codigo, $cantidad);
    if ($actualizado) {
        header('Location: carrito.php');
        exit;
    } else {
          $_SESSION['errores'][] = "Error: cantidad no válida o artículo no encontrado.";
    }
}
?>
