<?php

function cargar_carrito($id_usuario)
{
    global $pdo;

    // Cargar los productos del carrito desde la base de datos
    $query = "SELECT * FROM carrito WHERE id_usuario = :id_usuario";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();

    //Iniciamos el carrito en la sesión
    $_SESSION['carrito'] = [];

    // Recorrer los productos del carrito y cargar su contenido
    while ($producto = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Obtener los detalles del producto
        $query_producto = "SELECT * FROM productos WHERE codigo = :codigo";
        $stmt_producto = $pdo->prepare($query_producto);
        $stmt_producto->bindParam(':codigo', $producto['codigo_producto'], PDO::PARAM_STR);
        $stmt_producto->execute();
        $producto_detalle = $stmt_producto->fetch(PDO::FETCH_ASSOC);

        // Añadir el producto al carrito en la sesión
        $_SESSION['carrito'][] = [
            'codigo' => $producto['codigo_producto'],
            'nombre' => $producto_detalle['nombre'],
            'descripcion' => $producto_detalle['descripcion'],
            'imagen' => basename($producto_detalle['imagen']),
            'precio' => $producto_detalle['precio'],
            'cantidad' => $producto['cantidad'],
            'descuento' => $producto_detalle['descuento'] ?? 0,
            'precio_final' => $producto_detalle['precio'] * (1 - ($producto_detalle['descuento'] ?? 0) / 100)
        ];
    }
}


function guardar_carrito($id_usuario)
{
    global $pdo;
    // Verifica si el carrito está vacío
    if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
        return false;
    }
    //Borra el carrito anterior en la base de datos si existe
    $query_borrar = "DELETE FROM carrito WHERE id_usuario = :id_usuario";
    $stmt_borrar = $pdo->prepare($query_borrar);
    $stmt_borrar->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt_borrar->execute();
    //Inserta los productos del carrito en la base de datos
    foreach ($_SESSION['carrito'] as $producto) {

        $query_insertar = "INSERT INTO carrito (id_usuario, codigo_producto, cantidad) 
                            VALUES (:id_usuario, :codigo_producto, :cantidad)";
        $stmt_insertar = $pdo->prepare($query_insertar);
        $stmt_insertar->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt_insertar->bindParam(':codigo_producto', $producto['codigo'], PDO::PARAM_STR);
        $stmt_insertar->bindParam(':cantidad', $producto['cantidad'], PDO::PARAM_INT);
        $stmt_insertar->execute();
    }
    return true;
}


function actualizar_carrito($codigo, $cantidad, $pdo)
{
    //Comprobar si la cantidad es válida
    if ($cantidad <= 0) {
        return false;
    }
    // Verificar si el carrito está en la sesión
    if (isset($_SESSION['carrito'])) {
        foreach ($_SESSION['carrito'] as $llave => $producto) {
            if ($producto['codigo'] == $codigo) {
                // Actualiza la cantidad en la sesión
                $_SESSION['carrito'][$llave]['cantidad'] = $cantidad;
                $query = "UPDATE carrito SET cantidad = :cantidad WHERE id_usuario = :id_usuario AND codigo_producto = :codigo_producto";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
                $stmt->bindParam(':id_usuario', $_SESSION['id'], PDO::PARAM_INT);
                $stmt->bindParam(':codigo_producto', $codigo, PDO::PARAM_STR);
                $stmt->execute();
                return true;
            }
        }
    }
    return false;
}

function eliminar_producto_carrito($codigo, $pdo)
{
    // Verificar si el carrito está en la sesión
    if (isset($_SESSION['carrito'])) {
        foreach ($_SESSION['carrito'] as $llave => $producto) {
            if ($producto['codigo'] == $codigo) {
                // Eliminar de la sesión
                unset($_SESSION['carrito'][$llave]);

                // Ahora eliminamos el producto de la base de datos
                $query = "DELETE FROM carrito WHERE id_usuario = :id_usuario AND codigo_producto = :codigo_producto";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':id_usuario', $_SESSION['id'], PDO::PARAM_INT);
                $stmt->bindParam(':codigo_producto', $codigo, PDO::PARAM_STR);
                $stmt->execute();

                return true;
            }
        }
    }
    return false;
}

// Eliminar todos los productos del carrito en la base de datos
function vaciar_carrito_base_datos($id_usuario, $pdo)
{
    $query = "DELETE FROM carrito WHERE id_usuario = :id_usuario";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    return $stmt->execute();
}
