<?php
// Verificar si se ha enviado una actualización
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['codigo']) && isset($_POST['cantidad'])) {
    $codigo = $_POST['codigo']; //Recibimos el codigo y lo asginamos a la variable
    $cantidad = (int) $_POST['cantidad']; //Recibimos la cantidad y la asignamos a la variable (asegurandonos que sea entero).

    //Llamamos a la funcion de actualizar carrito y le pasamos el codigo, la cantidad y la conexión de la bbdd
    $actualizado = actualizar_carrito($codigo, $cantidad, $pdo);

    //Si se ha actualizado redirigimos al carrito
    if ($actualizado) {
        header('Location: carrito.php');
        exit();
    } else {
        echo "Error: cantidad no válida o artículo no encontrado.";
    }
}

//Recibimos el codigo del producto a elimninar desde ver_carrito
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['eliminar_producto'])) {
    $codigo = $_POST['eliminar_producto'];
    //Llamamos a la funcion de eliminar producto del carrito y le pasamos el codigo a eliminar
    $eliminado = eliminar_producto_carrito($codigo, $pdo);
    // Si se ha eliminado redirigimos al carrito
    if ($eliminado) {
        //Volvemos al carrito con js porue con header me daba problemas
        echo "<script type='text/javascript'> window.location.href = 'carrito.php';</script>";
        exit();
    } else {
        echo "Error: el producto no se pudo eliminar.";
    }
}
