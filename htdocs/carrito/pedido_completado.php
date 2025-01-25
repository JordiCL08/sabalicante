<?php
session_start();
include('../config/conectar_db.php');
include_once '../gestores/gestor_pedidos.php';

$pdo = conectar_db();
$gestorPedido = new GestorPedidos($pdo);

//Regenerar el ID de la sesión para mayor seguridad
session_regenerate_id();

if (isset($_SESSION['carrito']) && count($_SESSION['carrito']) > 0) {
    //Calculamos el total del pedido
    $total = 0;
    foreach ($_SESSION['carrito'] as $item) {
        if (!isset($item['precio_final'], $item['cantidad'], $item['codigo']) ||  !is_numeric($item['cantidad']) || !is_numeric($item['precio_final'])) {
            $_SESSION['errores'][] = "Hay algún problema con el pedido. Ponte en contacto con info@sabalicante.es .";
            header("Location: ../carrito.php");
            exit();
        }
        $total += $item['precio_final'] * $item['cantidad'];
    }
    $forma_pago =  $_SESSION['forma_pago'];
    $fecha_pedido = date('Y-m-d');
    // Iniciar la transacción
    $pdo->beginTransaction();
    try {
        //Añadimos el pedido
        $pedido_id = $gestorPedido->agregar_pedido($_SESSION['id'], $total, $forma_pago);
        //Añadimos las líneas del pedido
        $gestorPedido->agregar_linea_pedido($pedido_id, $_SESSION['carrito']);
        //Verificar y descontar stock
        foreach ($_SESSION['carrito'] as $item) {
            $stock = $gestorPedido->stock_actual($item['codigo']);
            if ($gestorPedido->verificar_stock($item['codigo'], $item['cantidad'])) {
                $gestorPedido->descontar_stock($item['codigo'], $item['cantidad']);
            } else {
                //Si el stock no es suficiente, revertir la transacción 
                $pdo->rollBack();
                $stock_actual = $stock['stock'];
                $_SESSION['errores'][] = "No hay suficiente stock del producto " . $item['nombre'] . ". El Stock actual es de: " . $stock_actual;
                header("Location: ../carrito.php");
                exit();
            }
        }
        // Confirmar la transacción
        $pdo->commit();
        $_SESSION['fecha_pedido'] = $fecha_pedido;
        $_SESSION['id_pedido'] = $pedido_id;
        unset($_SESSION['carrito']);  // Limpiar el carrito
        header("Location: ../pedido_confirmado.php");
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['errores'][] =  "Error al realizar el pedido. Por favor, intente nuevamente más tarde.";
    }
} else {
    $errores[] =  "No hay productos en el carrito.";
}
