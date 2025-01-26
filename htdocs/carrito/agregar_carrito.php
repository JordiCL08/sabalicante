<?php
session_start();
include('../config/conectar_db.php');

try {
    $pdo = conectar_db();

    if (isset($_POST['codigo']) && isset($_POST['cantidad'])) {
        $codigo_producto = $_POST['codigo'];
        $cantidad = (int)$_POST['cantidad'];
        //Comprobamos que sea un número y que sea mayor de 0 
        if (!is_numeric($cantidad) || $cantidad <= 0) {
            throw new Exception("La cantidad debe ser mayor que cero.");
        }

        // Obtener los detalles del producto
        $consulta = "SELECT * FROM productos WHERE codigo = :codigo";
        $stmt = $pdo->prepare($consulta);
        $stmt->bindParam(':codigo', $codigo_producto, PDO::PARAM_STR);
        $stmt->execute();
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($producto) {
            // Validar el stock 
            if ($producto['stock'] < $cantidad) {
                throw new Exception("No hay suficiente stock.");
            }
            if (isset($producto['descuento']) && $producto['descuento'] > 0) {
                // Calcular el precio con el descuento
                $descuento = $producto['descuento'];
                $precio_final = $producto['precio'] * (1 - $descuento / 100);
            } else {
                $precio_final = $producto['precio'];
            }
            // Guardar el precio final en la sesión
            $_SESSION['precio_final'] = $precio_final;
            // Verificar si el carrito ya está creado
            $_SESSION['carrito'] ??= [];

            // Verificar si el producto ya está en el carrito
            $producto_en_carrito = false;
            foreach ($_SESSION['carrito'] as &$item) {
                if ($item['codigo'] == $producto['codigo']) {
                    $item['cantidad'] += $cantidad; // Aumentar la cantidad
                    $producto_en_carrito = true;
                    break;
                }
            }

            // Si no está en el carrito, añadirlo
            if (!$producto_en_carrito) {
                $_SESSION['carrito'][] = [
                    'codigo' => $producto['codigo'],
                    'nombre' => $producto['nombre'],
                    'descripcion' => $producto['descripcion'],
                    'imagen' => basename($producto['imagen']),//evita inyeccion de archivos
                    'precio' => $producto['precio'],
                    'cantidad' => $cantidad,
                    'descuento' => isset($producto['descuento']) ? $producto['descuento'] : 0,
                    'precio_final' => $precio_final
                ];
            }

            // Mensaje de éxito
            $_SESSION['mensaje'] = "Producto añadido al carrito.";
        } else {
            throw new Exception("El producto no existe.");
        }
    } else {
        throw new Exception("Código o cantidad no especificados.");
    }
} catch (Exception $e) {
    // Guardar el mensaje de error en la sesión
    $_SESSION['errores'][] = "Error: " . $e->getMessage();
}

// Redirigir al carrito
header("Location: ../index.php?" . $_SERVER['QUERY_STRING']); // Mantener los filtros en la URL
exit();
