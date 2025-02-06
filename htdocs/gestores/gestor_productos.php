<?php
include_once(__DIR__ . '/../config/funciones.php');

// Clase Producto
class Producto
{

    private $codigo;
    private $nombre;
    private $descripcion;
    private $id_subfamilia;
    private $precio;
    private $imagen;
    private $descuento;
    private $activo;
    private $stock;

    // Constructor
    public function __construct($codigo, $nombre, $descripcion, $id_subfamilia, $precio, $imagen, $descuento, $activo, $stock)
    {
        $this->codigo = $codigo;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->id_subfamilia = $id_subfamilia;
        $this->precio = $precio;
        $this->imagen = $imagen;
        $this->descuento = $descuento;
        $this->activo = $activo;
        $this->stock = $stock;
    }

    // Métodos Getters y Setters
    public function getCodigo()
    {
        return $this->codigo;
    }
    public function getNombre()
    {
        return $this->nombre;
    }
    public function getDescripcion()
    {
        return $this->descripcion;
    }
    public function getIdSubFamilia()
    {
        return $this->id_subfamilia;
    }
    public function getPrecio()
    {
        return $this->precio;
    }
    public function getImagen()
    {
        return $this->imagen;
    }
    public function getDescuento()
    {
        return $this->descuento;
    }
    public function getActivo()
    {
        return $this->activo;
    }
    public function getStock()
    {
        return $this->stock;
    }
}

// Clase GestorProductos
class GestorProductos
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Mostrar productos con paginación y ordenación
    public function mostrar_productos($buscar_producto = '', $ordenar = 'ASC', $familia = null, $subfamilia = null)
    {
        try {
            //Número de productos por página
            $registros = 6;
            //Página actual
            $pagina = isset($_GET["pagina"]) && is_numeric($_GET["pagina"]) && $_GET["pagina"] > 0 ? (int)$_GET["pagina"] : 1;
            $inicio = ($pagina - 1) * $registros;
            //Condiciones de búsqueda
            $condiciones = ["productos.nombre LIKE :buscar_producto"];
            //Si buscamos por familia
            if ($familia) $condiciones[] = "subfamilias.id_familia = :familia";
            //Si buscamos por subfamilia
            if ($subfamilia) $condiciones[] = "productos.id_subfamilia = :subfamilia";
            //Unimos las condiciones de busqueda para aplicarlo en la consulta
            $condiciones_sql = implode(' AND ', $condiciones);
            $query = "SELECT COUNT(*) FROM productos 
                      JOIN subfamilias ON productos.id_subfamilia = subfamilias.id_subfamilia 
                      WHERE $condiciones_sql";

            //Consultar el número total de productos
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':buscar_producto', '%' . $buscar_producto . '%');
            if ($familia) $stmt->bindValue(':familia', $familia, PDO::PARAM_INT);
            if ($subfamilia) $stmt->bindValue(':subfamilia', $subfamilia, PDO::PARAM_INT);
            $stmt->execute();
            $num_total_registros = $stmt->fetchColumn();
            $total_paginas = ceil($num_total_registros / $registros);

            //Por defecto ordena por nombre, si recibe que se ordena por precio, esto cambia y ordena por precio
            $campo_orden = isset($_GET['campo_orden']) && $_GET['campo_orden'] === 'precio' ? 'productos.precio' : 'productos.nombre';

            //Obtener los productos según los filtros y la paginación
            $query = "SELECT productos.* FROM productos 
                      JOIN subfamilias ON productos.id_subfamilia = subfamilias.id_subfamilia
                      WHERE $condiciones_sql 
                      ORDER BY $campo_orden $ordenar LIMIT :inicio, :registros";
            //Preparamos la consulta
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':buscar_producto', '%' . $buscar_producto . '%');
            if ($familia) $stmt->bindValue(':familia', $familia, PDO::PARAM_INT);
            if ($subfamilia) $stmt->bindValue(':subfamilia', $subfamilia, PDO::PARAM_INT);
            $stmt->bindValue(':inicio', $inicio, PDO::PARAM_INT);
            $stmt->bindValue(':registros', $registros, PDO::PARAM_INT);
            $stmt->execute();
            // Obtener los productos
            $resultado = $stmt->fetchAll(PDO::FETCH_OBJ);
            $productos = [];
            foreach ($resultado as $producto) {
                $productos[] = new Producto(
                    $producto->codigo,
                    $producto->nombre,
                    $producto->descripcion,
                    $producto->id_subfamilia,
                    $producto->precio,
                    $producto->imagen,
                    $producto->descuento,
                    $producto->activo,
                    $producto->stock
                );
            }

            return [$productos, $total_paginas];
        } catch (PDOException $e) {
            throw new Exception('Error al obtener productos: ' . $e->getMessage());
        }
    }

    // Borrar un producto por código
    public function borrar_producto($codigo)
    {
        // Obtener la imagen del producto
        $query = "SELECT imagen FROM productos WHERE codigo = :codigo";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':codigo', $codigo);
        $stmt->execute();
        $producto = $stmt->fetch(PDO::FETCH_OBJ);
        // Si el producto tiene imagen, eliminarla
        if ($producto && $producto->imagen) {
            $ruta_imagen = "imagenes/" . $producto->imagen;
            //Verificamos si existe y eliminamos el archivo de la ruta
            if (file_exists($ruta_imagen) && unlink($ruta_imagen)) {
                echo "Imagen eliminada con éxito.";
            } else {
                echo "Error al eliminar la imagen o no existe.";
            }
        } else {
            echo "No se encontró la imagen del producto.";
        }
        // Eliminar el producto de la base de datos
        try {
            $query = "DELETE FROM productos WHERE codigo = :codigo";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':codigo', $codigo);
            if ($stmt->execute()) {
                return "Producto eliminado con éxito.";
            }
            return "No se pudo eliminar el producto.";
        } catch (PDOException $e) {
            throw new Exception('Error al eliminar producto: ' . $e->getMessage());
        }
    }


    // Crear un nuevo producto
    public function crear_producto(Producto $producto)
    {
        $query = "INSERT INTO productos (codigo, nombre, descripcion, id_subfamilia, precio, imagen, descuento, stock) 
                  VALUES (:codigo, :nombre, :descripcion, :id_subfamilia, :precio, :imagen, :descuento, :stock)";
        try {
            //Preparamos la consulta
            $stmt = $this->pdo->prepare($query);
            //Vinculamos los parametros
            $stmt->bindValue(':codigo', $producto->getCodigo());
            $stmt->bindValue(':nombre', $producto->getNombre());
            $stmt->bindValue(':descripcion', $producto->getDescripcion());
            $stmt->bindValue(':id_subfamilia', $producto->getIdSubFamilia());
            $stmt->bindValue(':precio', $producto->getPrecio());
            $stmt->bindValue(':imagen', $producto->getImagen());
            $stmt->bindValue(':descuento', $producto->getDescuento());
            $stmt->bindValue(':stock', $producto->getStock());
            //Ejecutamos la consulta y si sale bién, se crea el producto
            if ($stmt->execute()) {
                return "Producto añadido con éxito.";
            } else {
                return "Error al crear el producto.";
            }
        } catch (PDOException $e) {
            throw new Exception('Error al crear producto: ' . $e->getMessage());
        }
    }

    // Editar un producto existente
    public function editar_producto(Producto $producto)
    {
        $query = "UPDATE productos SET nombre = :nombre, descripcion = :descripcion, id_subfamilia = :id_subfamilia, precio = :precio, imagen = :imagen, descuento = :descuento, activo = :activo, stock = :stock WHERE codigo = :codigo";
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':codigo', $producto->getCodigo());
            $stmt->bindValue(':nombre', $producto->getNombre());
            $stmt->bindValue(':descripcion', $producto->getDescripcion());
            $stmt->bindValue(':id_subfamilia', $producto->getIdSubFamilia());
            $stmt->bindValue(':precio', $producto->getPrecio());
            $stmt->bindValue(':imagen', $producto->getImagen());
            $stmt->bindValue(':descuento', $producto->getDescuento());
            $stmt->bindValue(':activo', $producto->getActivo());
            $stmt->bindValue(':stock', $producto->getStock());
            if ($stmt->execute()) {
                return "Producto editado con éxito.";
            } else {
                return "Error al editar el producto.";
            }
        } catch (PDOException $e) {
            throw new Exception('Error al editar producto: ' . $e->getMessage());
        }
    }

    // Obtener producto por código
    public function obtener_producto_codigo($codigo)
    {
        $query = 'SELECT * FROM productos WHERE codigo = :codigo';

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['codigo' => $codigo]);
            $producto = $stmt->fetch(PDO::FETCH_OBJ);
            if ($producto !== false) {
                return new Producto(
                    $producto->codigo,
                    $producto->nombre,
                    $producto->descripcion,
                    $producto->id_subfamilia,
                    $producto->precio,
                    $producto->imagen,
                    $producto->descuento,
                    $producto->activo,
                    $producto->stock
                );
            }
            return null;
        } catch (PDOException $e) {
            throw new Exception('Error al obtener el producto: ' . $e->getMessage());
        }
    }
    //Función para obtener las familias asociadas a un producto
    public function obtener_familias($codigo_producto)
    {
        try {
            $query = "SELECT f.nombre AS familia_nombre
                FROM familias f
                INNER JOIN subfamilias sf ON sf.id_familia = f.id_familia
                INNER JOIN productos p ON p.id_subfamilia = sf.id_subfamilia
                WHERE p.codigo = :codigo_producto";

            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':codigo_producto', $codigo_producto);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Manejo de errores
            return ['error' => 'Error en la consulta: ' . $e->getMessage()];
        }
    }
    //Función para obtener las subfamilias asociadas a un producto
    public function obtener_subfamilias($codigo_producto)
    {
        try {
            $query = "SELECT sf.id_subfamilia, sf.nombre AS subfamilia_nombre
                    FROM subfamilias sf
                    INNER JOIN productos p ON p.id_subfamilia = sf.id_subfamilia
                    WHERE p.codigo = :codigo_producto";

            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':codigo_producto', $codigo_producto);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Manejo de errores
            return ['error' => 'Error en la consulta: ' . $e->getMessage()];
        }
    }

    public function obtener_subfamilias_visor()
    {
        try {
            // Consulta para obtener todas las familias activas
            $query = "SELECT id_subfamilia, nombre FROM subfamilias WHERE activo = 1"; // Filtrando las subfamilias activas
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();

            // Obtener todas las subfamilias
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception('Error al obtener subfamilias: ' . $e->getMessage());
        }
    }
    public function top_ventas()
    {
        try {
            $query = "SELECT codigo_producto, SUM(cantidad) AS total_vendido FROM lineas_pedido GROUP BY codigo_producto ORDER BY total_vendido desc limit 5";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception('Error al obtener top ventas: ' . $e->getMessage());
        }
    }
}
