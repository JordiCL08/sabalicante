<?php
session_start();
include('../config/conectar_db.php');
include('../gestores/gestor_carritos.php');
include('../gestores/gestor_productos.php'); 

try {
    $pdo = conectar_db();
    $gestorProducto = new GestorProductos($pdo);

    if (isset($_POST['codigo']) && isset($_POST['cantidad'])) {
        $codigo_producto = $_POST['codigo'];
        $cantidad = (int)$_POST['cantidad'];
        
        //Comprobamos que sea un número y que sea mayor de 0 
        if (!is_numeric($cantidad) || $cantidad <= 0) {
            throw new Exception("La cantidad debe ser mayor que cero.");
        }

        //Obtenemos la información del producto
        $producto = $gestorProducto->obtener_producto_codigo($codigo_producto);

        if ($producto) {
            // Validar el stock 
            if ($producto->getStock() < $cantidad) {
                throw new Exception("No hay suficiente stock.");
            }
            //Calcular el precio final con descuento  en caso de que lo tenga
            $precio_final = $producto->getPrecio();
            if ($producto->getDescuento() > 0) {
                $precio_final *= (1 - $producto->getDescuento() / 100);
            }

            //Guardar el precio final en la sesión
            $_SESSION['precio_final'] = $precio_final;
            
            //Verificar si el carrito ya está creado
            $_SESSION['carrito'] ??= [];

            //Verificar si el producto ya está en el carrito
            $producto_en_carrito = false;
            foreach ($_SESSION['carrito'] as &$item) {
                if ($item['codigo'] == $producto->getCodigo()) {
                    $item['cantidad'] += $cantidad; // Aumentar la cantidad
                    $producto_en_carrito = true;
                    break;
                }
            }
            // Si no está en el carrito, añadirlo
            if (!$producto_en_carrito) {
                $_SESSION['carrito'][] = [
                    'codigo' => $producto->getCodigo(),
                    'nombre' => $producto->getNombre(),
                    'descripcion' => $producto->getDescripcion(),
                    'imagen' => basename($producto->getImagen()), // Evita inyección de archivos
                    'precio' => $producto->getPrecio(),
                    'cantidad' => $cantidad,
                    'descuento' => $producto->getDescuento(),
                    'precio_final' => $precio_final
                ];
            }
            // Si el usuario está logueado, guardar el carrito en la base de datos
            if (isset($_SESSION['id'])) {
                $usuario_id = $_SESSION['id'];

                // Llamamos a la función para guardar el carrito en la base de datos
                if (guardar_carrito($usuario_id)) {
                    $_SESSION['mensaje'] = "Producto añadido al carrito.";
                } else {
                    $_SESSION['mensaje'] = "Error al guardar el carrito.";
                }
            } else {
                $_SESSION['mensaje'] = "Producto añadido al carrito.";
            }

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
header("Location: ../index.php?" . $_SERVER['QUERY_STRING']); //Mantener los filtros en la URL
exit();