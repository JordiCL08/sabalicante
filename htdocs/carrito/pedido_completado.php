<?php
session_start();
include('../config/conectar_db.php');
include_once '../gestores/gestor_pedidos.php';
include_once '../gestores/gestor_carritos.php';
$pdo = conectar_db();
$gestorPedido = new GestorPedidos($pdo);

//Cambia el ID de la sesión para mayor seguridad
session_regenerate_id();

if (isset($_SESSION['carrito']) && count($_SESSION['carrito']) > 0) {
    //Calculamos el total del pedido
    $total = 0;
    foreach ($_SESSION['carrito'] as $producto) {
        //Si el precio final y la cantidad estan vacios o no son un numero nos da error.
        if (empty($producto['precio_final']) || empty($producto['cantidad']) || !is_numeric($producto['cantidad']) || !is_numeric($producto['precio_final'])) {
            $_SESSION['errores'][] = "Hay algún problema con el pedido. Ponte en contacto con info@sabalicante.es .";
            header("Location: ../carrito.php");
            exit();
        }
        //En el caso de que no de el error, sacamos el total
        $total += $producto['precio_final'] * $producto['cantidad'];
    }
    //**EXTRAEMOS DE LA SESION LA FORMA DE PAGO Y LOS GASTOS DE ENVIO  QUE SE RECIBEN EN EL CHECKOUT*/
    $forma_pago =  $_SESSION['forma_pago'];
    $gastos_envio =  $_SESSION['gastos_envio']; //sumaria 5 al total
    /****************************************************************/
    //Fecha en la que se hace el pedido
    $fecha_pedido = date('Y-m-d');
    //En el caso de que haya gastos de envio
    if ($gastos_envio > 0) {
        $recogida_local = false;
    } else {
        $recogida_local = true;
    }
    // Iniciar la transacción
    $pdo->beginTransaction();
    try {
        // Añadimos el pedido
        $pedido_id = $gestorPedido->agregar_pedido($_SESSION['id'], $total, $forma_pago, $gastos_envio, $recogida_local);

        // Añadimos las líneas del pedido
        $gestorPedido->agregar_linea_pedido($pedido_id, $_SESSION['carrito']);

        // Verificar y descontar stock
        foreach ($_SESSION['carrito'] as $producto) {
            $stock = $gestorPedido->stock_actual($producto['codigo']);
            if ($gestorPedido->verificar_stock($producto['codigo'], $producto['cantidad'])) {
                $gestorPedido->descontar_stock($producto['codigo'], $producto['cantidad']);
            } else {
                // Si el stock no es suficiente, revertir la transacción 
                $pdo->rollBack();
                $stock_actual = $stock['stock'];
                escribir_log("Error con el pedido = ID Pedido: $pedido_id realizado por el usuario: " . $_SESSION['usuario'] . " por falta de stock en el producto: " . $producto['nombre'], 'pedidos');
                $_SESSION['errores'][] = "No hay suficiente stock del producto " . $producto['nombre'] . ". El Stock actual es de: " . $stock_actual;
                header("Location: ../carrito.php");
                exit();
            }
        }

        // Confirmar la transacción
        $pdo->commit();
        $_SESSION['fecha_pedido'] = $fecha_pedido;
        $_SESSION['id_pedido'] = $pedido_id;
        unset($_SESSION['carrito']);  // Limpiar el carrito
        vaciar_carrito_base_datos($_SESSION['id'], $pdo);
        escribir_log("ID Pedido: $pedido_id completado con exito por el usuario: " . $_SESSION['usuario'], 'pedidos');
        //Redirigir a la página de confirmación del pedido
        header("Location: ../pedido_confirmado.php");
    } catch (Exception $e) {
        $pdo->rollBack();
        escribir_log("Error con el pedido = ID Pedido: $pedido_id realizado por el usuario: " . $_SESSION['usuario'], 'pedidos');
        $_SESSION['errores'][] =  "Error al realizar el pedido. Por favor, intente nuevamente más tarde.";
    }
} else {
    $errores[] =  "No hay productos en el carrito.";
}
