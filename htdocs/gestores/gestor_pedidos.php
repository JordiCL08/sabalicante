<?php
include_once(__DIR__ . '/../config/funciones.php');
include_once 'gestor_productos.php';
class GestorPedidos
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }
    //Agregar un pedido
    public function agregar_pedido($id_usuario, $total, $forma_pago,$gastos_envio,$recogida_local)
    {
        //Calculamos el total con los gastos de envio
        $total_con_envio = $total + $gastos_envio;
        $query = "INSERT INTO pedidos (id_usuario, fecha, total, forma_pago,recogida_local) VALUES (:id_usuario, NOW(), :total, :forma_pago ,:recogida_local)";
        //Preparamos la consulta
        $stmt = $this->pdo->prepare($query);
        //Vinculamos los parametros
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->bindParam(':total', $total_con_envio);
        $stmt->bindParam(':forma_pago', $forma_pago, PDO::PARAM_STR);
        $stmt->bindParam(':recogida_local', $recogida_local, PDO::PARAM_BOOL);
        //Ejecutamos la consulta
        $stmt->execute();
        // Devuelve el ID del ultimo pedido
        return $this->pdo->lastInsertId();
    }

    //Agregar las líneas de pedido
    public function agregar_linea_pedido($pedido_id, $carrito)
    {
        foreach ($carrito as $item) {

            $gestorProducto = new GestorProductos($this->pdo);
            // Obtener los detalles del producto (incluyendo descuento)
            $producto = $gestorProducto->obtener_producto_codigo($item['codigo']);

            // Verificar si el producto existe y tiene descuento
            if ($producto) {
                // Si hay un descuento, aplicarlo
                $precio_con_descuento = $producto->getPrecio();
                if ($producto->getDescuento() > 0) {
                    $precio_con_descuento *= (1 - $producto->getDescuento() / 100); // Aplica el descuento al precio
                }
                // Calcular el subtotal con el precio con descuento
                $subtotal = $precio_con_descuento * $item['cantidad'];
                // Insertar la línea en la base de datos con el precio con descuento
                $query = "INSERT INTO lineas_pedido (id_pedido, codigo_producto, cantidad, precio_unitario, subtotal) 
                      VALUES (:pedido_id, :codigo_producto, :cantidad, :precio_unitario, :subtotal)";
                //Preparamos consulta
                $stmt = $this->pdo->prepare($query);
                //Vinculamos parametros
                $stmt->bindParam(':pedido_id', $pedido_id, PDO::PARAM_INT);
                $stmt->bindParam(':codigo_producto', $item['codigo'], PDO::PARAM_STR);
                $stmt->bindParam(':cantidad', $item['cantidad'], PDO::PARAM_INT);
                $stmt->bindParam(':precio_unitario', $precio_con_descuento);
                $stmt->bindParam(':subtotal', $subtotal);
                //Ejecutamos consulta
                $stmt->execute();
            } else {
                // Si no se encuentra el producto, manejar el error (opcional)
                throw new Exception("Producto no encontrado: " . $item['codigo']);
            }
        }
    }


    public function obtener_pedido_usuario($id_usuario)
    {
        $query = "SELECT id_pedido, fecha,  estado, total FROM pedidos WHERE id_usuario = :id_usuario ORDER BY fecha DESC";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener_pedido_id_pedido($id_pedido)
    {
        $query = "SELECT id_pedido, fecha, total, estado FROM pedidos WHERE id_pedido = :id_pedido";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id_pedido', $id_pedido, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function mostrar_pedidos($ordenar = 'ASC', $buscar = '')
    {
        $registros = 10;
        $pagina = isset($_GET["pagina"]) && is_numeric($_GET["pagina"]) && $_GET["pagina"] > 0 ? (int)$_GET["pagina"] : 1;
        $inicio = ($pagina - 1) * $registros;
    
        // Consulta para obtener los pedidos con búsqueda por email
        $query = "SELECT p.id_pedido, p.id_usuario, p.fecha, p.estado, p.total, p.recogida_local, u.email 
                  FROM pedidos p
                  JOIN usuarios u ON p.id_usuario = u.id";
        
        // Filtro de búsqueda en el caso de que lo haya
        if ($buscar) {
            $query .= " WHERE u.email LIKE :buscar";
        }
        // Ordenación y paginación
        $query .= " ORDER BY u.email $ordenar LIMIT :inicio, :registros";
        // Preparamos y ejecutamos la consulta
        $stmt = $this->pdo->prepare($query);
        if ($buscar) {
            $stmt->bindValue(':buscar', "%$buscar%");
        }
        $stmt->bindValue(':inicio', $inicio, PDO::PARAM_INT);
        $stmt->bindValue(':registros', $registros, PDO::PARAM_INT);
        $stmt->execute();
        // Obtener los resultados
        $pedidos = $stmt->fetchAll(PDO::FETCH_OBJ);
        // Calcular el total de registros para la paginación
        $query_total = "SELECT COUNT(*) FROM pedidos p JOIN usuarios u ON p.id_usuario = u.id";
        if ($buscar) {
            $query_total .= " WHERE u.email LIKE :buscar";
        }
        //Obtener el total de registros
        $stmt_total = $this->pdo->prepare($query_total);
        if ($buscar) {
            $stmt_total->bindValue(':buscar', "%$buscar%");
        }
        $stmt_total->execute();
        $total_registros = $stmt_total->fetchColumn();
        // Calcular el total de páginas
        $total_paginas = ceil($total_registros / $registros);
        // Devolver los pedidos y el total de páginas
        return [$pedidos, $total_paginas];
    }

    public function obtener_lineas_pedido($id_pedido)
    {
        $query = "SELECT p.codigo, p.nombre, p.descripcion, p.imagen, lp.cantidad, lp.precio_unitario, lp.subtotal, pd.recogida_local
                  FROM lineas_pedido lp
                  JOIN productos p ON lp.codigo_producto = p.codigo
                  JOIN pedidos pd ON lp.id_pedido = pd.id_pedido
                  WHERE lp.id_pedido = :id_pedido";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id_pedido', $id_pedido, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    

    public function verificar_stock($codigo_producto, $cantidad_solicitada)
    {
        // Consultamos el stock disponible en la base de datos
        $query = "SELECT stock FROM productos WHERE codigo = :codigo_producto";
        $stmt = $this->pdo->prepare($query);
        //Ejecutamos la consulta pasando el valor del parametro
        $stmt->execute([':codigo_producto' => $codigo_producto]);
        //Obtenemos el stock del producto
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$producto || $producto['stock'] < $cantidad_solicitada) {
            return false; //No hay suficiente stock
        }
        return true; //Hay suficiente stock
    }

    public function stock_actual($codigo_producto) {
        $query = "SELECT stock FROM productos WHERE codigo = :codigo_producto";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':codigo_producto' => $codigo_producto]);
        return $stmt->fetch(PDO::FETCH_ASSOC);  
    }
    
    public function descontar_stock($codigo_producto, $cantidad)
    {
        $query = "UPDATE productos SET stock = stock - :cantidad WHERE codigo = :codigo_producto";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':codigo_producto', $codigo_producto, PDO::PARAM_STR);
        $stmt->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function eliminar_pedido($id_pedido)
    {
        $query = "DELETE FROM pedidos WHERE id_pedido = :id_pedido";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id_pedido', $id_pedido, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function actualizar_estado_pedido($id_pedido, $estado)
    {
        $query = "UPDATE pedidos SET estado = :estado WHERE id_pedido = :id_pedido";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id_pedido', $id_pedido, PDO::PARAM_INT);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
        $stmt->execute();
    }
}
