<?php
session_start();
// Verificar que se haya enviado un formulario POST y que exista el índice del producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['index'])) {
    $index = $_POST['index'];

    // Verificar si el índice existe en el carrito
    if (isset($_SESSION['carrito'][$index])) {
        // Eliminar el producto del carrito
        unset($_SESSION['carrito'][$index]);

        // Reindexar el arreglo para evitar huecos en los índices del carrito
        $_SESSION['carrito'] = array_values($_SESSION['carrito']);
    }
}

// Redirigir de vuelta al carrito
header('Location: ../carrito.php');
exit;
