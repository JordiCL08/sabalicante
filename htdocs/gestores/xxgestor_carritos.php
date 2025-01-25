<?php

class Carrito
{
    private $id_carrito;
    private $id_usuario;
    private $codigo_producto;
    private $cantidad;

    private $pdo;
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    //Getters y Setters

    public function getIdCarrito()
    {
        return $this->id_carrito;
    }

    public function setIdCarrito($id_carrito)
    {
        $this->id_carrito = $id_carrito;
    }

    public function getIdUsuario()
    {
        return $this->id_usuario;
    }

    public function setIdUsuario($id_usuario)
    {
        $this->id_usuario = $id_usuario;
    }

    public function getCodigoProducto()
    {
        return $this->codigo_producto;
    }

    public function setCodigoProducto($codigo_producto)
    {
        $this->codigo_producto = $codigo_producto;
    }

    public function getCantidad()
    {
        return $this->cantidad;
    }

    public function setCantidad($cantidad)
    {
        // Validar que la cantidad sea un número positivo
        if ($cantidad > 0) {
            $this->cantidad = $cantidad;
        } else {
            throw new InvalidArgumentException("La cantidad debe ser mayor a cero.");
        }
    }

    //Agregar producto al carrito
    public function agregarProducto($id_usuario, $codigo_producto, $cantidad)
    {
        try {
            // Verificar si el producto ya existe en el carrito
            $stmt = $this->pdo->prepare("SELECT cantidad FROM carrito WHERE id_usuario = :id_usuario AND codigo_producto = :codigo_producto");
            $stmt->bindParam(':id_usuario', $id_usuario);
            $stmt->bindParam(':codigo_producto', $codigo_producto);
            $stmt->execute();
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($producto) {
                // Si el producto ya existe en el carrito, actualizar la cantidad
                $nueva_cantidad = $producto['cantidad'] + $cantidad;
                $update = $this->pdo->prepare("UPDATE carrito SET cantidad = :cantidad WHERE id_usuario = :id_usuario AND codigo_producto = :codigo_producto");
                $update->bindParam(':cantidad', $nueva_cantidad);
                $update->bindParam(':id_usuario', $id_usuario);
                $update->bindParam(':codigo_producto', $codigo_producto);
            } else {
                // Si el producto no existe en el carrito, insertarlo
                $insert = $this->pdo->prepare("INSERT INTO carrito (id_usuario, codigo_producto, cantidad) VALUES (:id_usuario, :codigo_producto, :cantidad)");
                $insert->bindParam(':id_usuario', $id_usuario);
                $insert->bindParam(':codigo_producto', $codigo_producto);
                $insert->bindParam(':cantidad', $cantidad);
            }
    
            // Ejecutar la consulta (ya sea de actualización o inserción)
            return ($producto ? $update : $insert)->execute();
        } catch (PDOException $e) {
            echo "Error al agregar el producto: " . $e->getMessage();
            return false;
        }
    }
    


    //Mostrar carrito
    public function mostrarCarrito($id_usuario)
    {
        try {
            $sql = "SELECT c.id_carrito, p.nombre, p.precio, c.cantidad, 
                           (p.precio * c.cantidad) AS subtotal
                    FROM carrito c
                    JOIN productos p ON c.codigo_producto = p.codigo
                    WHERE c.id_usuario = :id_usuario";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_usuario' => $id_usuario]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error al mostrar el carrito: " . $e->getMessage();
            return [];
        }
    }

    //Eliminar producto del carrito
    public function eliminarProducto($id_usuario, $codigo_producto)
    {
        try {
            $sql = "DELETE FROM carrito 
                    WHERE id_usuario = :id_usuario AND codigo_producto = :codigo_producto";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id_usuario' => $id_usuario,
                ':codigo_producto' => $codigo_producto,
            ]);

            return true;
        } catch (PDOException $e) {
            echo "Error al eliminar producto del carrito: " . $e->getMessage();
            return false;
        }
    }

    //Vaciar el carrito
    public function vaciarCarrito($id_usuario)
    {
        try {
            $sql = "DELETE FROM carrito WHERE id_usuario = :id_usuario";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_usuario' => $id_usuario]);

            return true;
        } catch (PDOException $e) {
            echo "Error al vaciar el carrito: " . $e->getMessage();
            return false;
        }
    }
    //Vaciar calcula el total
    public function calcularTotal($id_usuario)
    {
        try {
            $sql = "SELECT SUM(p.precio * c.cantidad) AS total
                FROM carrito c
                JOIN productos p ON c.codigo_producto = p.codigo
                WHERE c.id_usuario = :id_usuario";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_usuario' => $id_usuario]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'] ?? 0; // Si no hay productos, el total será 0
        } catch (PDOException $e) {
            error_log("Error al calcular el total del carrito: " . $e->getMessage());
            return 0;
        }
    }
}

class LineaPedido
{
    private $id_linea;
    private $id_pedido;
    private $codigo_producto;
    private $cantidad;
    private $precio_unitario;
    private $subtotal;
}

class Pedido
{
    private $id_pedido;
    private $id_usuario;
    private $fecha;

    private $estado;
    private $total;
}
