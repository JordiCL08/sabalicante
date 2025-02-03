<?php
include_once(__DIR__ . '/../config/funciones.php');
include_once 'gestor_productos.php';
class Pedido
{
    private $id_pedido;
    private $id_usuario;

    private $fecha_pedido;
    private $estado_pedido;

    private $total_pedido;

    private $forma_pago;

    private $recogida_local;

    public function __construct($id_pedido, $id_usuario, $fecha_pedido, $estado_pedido, $total_pedido, $forma_pago,$recogida_local)
    {
        $this->id_pedido = $id_pedido;
        $this->id_usuario = $id_usuario;
        $this->fecha_pedido = $fecha_pedido;
        $this->estado_pedido = $estado_pedido;
        $this->total_pedido = $total_pedido;
        $this->forma_pago = $forma_pago;
        $this->recogida_local = $recogida_local;
    }

    public function getIdPedido()
    {
        return $this->id_pedido;
    }
    public function getIdUsuario()
    {
        return $this->id_usuario;
    }
    public function getFechaPedido()
    {
        return $this->fecha_pedido;
    }
    public function getEstadoPedido()
    {
        return $this->estado_pedido;
    }
    public function getTotalPedido()
    {
        return $this->total_pedido;
    }
    public function getFormaPago()
    {
        return $this->forma_pago;
    }

    public function getRecogidaLocal()
    {
        return $this->recogida_local;
    }
}

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
        $total_con_envio = $total + $gastos_envio;
        $query = "INSERT INTO pedidos (id_usuario, fecha, total, forma_pago,recogida_local) VALUES (:id_usuario, NOW(), :total, :forma_pago ,:recogida_local)";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->bindParam(':total', $total_con_envio);
        $stmt->bindParam(':forma_pago', $forma_pago, PDO::PARAM_STR);
        $stmt->bindParam(':recogida_local', $recogida_local, PDO::PARAM_BOOL);
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
                $precioConDescuento = $producto->getPrecio();
                if ($producto->getDescuento() > 0) {
                    $precioConDescuento = $precioConDescuento * (1 - ($producto->getDescuento() / 100)); // Aplica el descuento al precio
                }

                // Calcular el subtotal con el precio con descuento
                $subtotal = $precioConDescuento * $item['cantidad'];

                // Insertar la línea en la base de datos con el precio con descuento
                $query = "INSERT INTO lineas_pedido (id_pedido, codigo_producto, cantidad, precio_unitario, subtotal) 
                      VALUES (:pedido_id, :codigo_producto, :cantidad, :precio_unitario, :subtotal)";
                $stmt = $this->pdo->prepare($query);
                $stmt->bindParam(':pedido_id', $pedido_id, PDO::PARAM_INT);
                $stmt->bindParam(':codigo_producto', $item['codigo'], PDO::PARAM_STR);
                $stmt->bindParam(':cantidad', $item['cantidad'], PDO::PARAM_INT);
                $stmt->bindParam(':precio_unitario', $precioConDescuento);
                $stmt->bindParam(':subtotal', $subtotal);
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

    public function mostrar_pedidos()
    {
        // Consulta para obtener todos los pedidos
        $query = "SELECT id_pedido, id_usuario, fecha, estado, total,recogida_local FROM pedidos";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        // Obtener todos los pedidos
        $pedidos = $stmt->fetchAll(PDO::FETCH_OBJ);
        // Calcular el total de páginas 
        $total_registros = count($pedidos);
        $registros_por_pagina = 10;
        $total_paginas = ceil($total_registros / $registros_por_pagina);

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
        $stmt->execute([':codigo_producto' => $codigo_producto]);
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
