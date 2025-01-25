<?php
session_start();
//Verificar que se haya enviado un formulario POST y que exista el índice del producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['index'])) {
    $index = $_POST['index'];
    // Verificar si el índice existe en el carrito
    if (isset($_SESSION['carrito'][$index])) {
        // Eliminar el producto del carrito
        unset($_SESSION['carrito'][$index]);

        $_SESSION['carrito'] = array_values($_SESSION['carrito']);
    }
}

header('Location: ../carrito.php');
exit;
