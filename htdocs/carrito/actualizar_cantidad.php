<?php
// Verificar si se ha enviado una actualización
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['codigo']) && isset($_POST['cantidad'])) {
    $codigo = $_POST['codigo'];
    $cantidad = (int) $_POST['cantidad']; // Asegurarse de que la cantidad es un número entero

    // Llamada a la función para actualizar el carrito
    $actualizado = actualizar_carrito($codigo, $cantidad, $pdo);

    // Si se ha actualizado correctamente, redirigir
    if ($actualizado) {
        header('Location: carrito.php');
        exit();
    } else {
        // Puedes manejar el error, como mostrar un mensaje
        echo "Error: cantidad no válida o artículo no encontrado.";
    }
}

// Eliminar producto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['eliminar'])) {
    $codigo = $_POST['eliminar'];
    $eliminado = eliminar_producto_carrito($codigo, $pdo);
    // Si se ha eliminado correctamente, redirigir
    if ($eliminado) {
        //Con js porue con header me daba problemas
        echo "<script type='text/javascript'>
                window.location.href = 'carrito.php';
              </script>";
        exit(); 
    } else {
        echo "Error: el producto no se pudo eliminar.";
    }
}
