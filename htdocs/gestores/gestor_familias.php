<?php
// Clase Familia

class Familia
{
 
    private $id_familia;
    private $nombre;
    private $descripcion;
    private $activo;

    public function __construct($id_familia = null, $nombre = null, $descripcion = null, $activo = 1)
    {
        $this->id_familia = $id_familia;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->activo = $activo;
    }

    public function getIdFamilia()
    {
        return $this->id_familia;
    }
    public function getNombre()
    {
        return $this->nombre;
    }
    public function getDescripcion()
    {
        return $this->descripcion;
    }
    public function getActivo()
    {
        return $this->activo;
    }

    public function setIdFamilia($id_familia)
    {
        $this->id_familia = $id_familia;
    }
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;
    }
    public function setActivo($activo)
    {
        $this->activo = $activo;
    }
}


// Clase GestorFamilias
class GestorFamilias
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Mostrar familias con paginación y ordenación
    public function mostrar_familias($buscar_familia = '', $ordenar = 'ASC')
    {
        try {
            // Validar que el parámetro 'ordenar' sea válido
            if (!in_array(strtoupper($ordenar), ['ASC', 'DESC'])) {
                throw new Exception('Orden no válido');
            }

            // Definir el número de registros por página
            $registros = 10;

            // Obtener el número de página actual
            $pagina = isset($_GET["pagina"]) && is_numeric($_GET["pagina"]) && $_GET["pagina"] > 0 ? (int)$_GET["pagina"] : 1;
            $inicio = ($pagina - 1) * $registros;

            // Crear la consulta base con el filtro de búsqueda (si existe)
            $query = "SELECT COUNT(*) FROM familias WHERE nombre LIKE :buscar_familia";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':buscar_familia', '%' . $buscar_familia . '%');
            $stmt->execute();
            $num_total_registros = $stmt->fetchColumn();
            $total_paginas = ceil($num_total_registros / $registros);

            // Consulta para obtener las familias con el filtro de búsqueda y orden
            $query = "SELECT * FROM familias WHERE nombre LIKE :buscar_familia ORDER BY nombre $ordenar LIMIT :inicio, :registros";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':buscar_familia', '%' . $buscar_familia . '%');
            $stmt->bindValue(':inicio', $inicio, PDO::PARAM_INT);
            $stmt->bindValue(':registros', $registros, PDO::PARAM_INT);
            $stmt->execute();

            // Obtener las familias
            $familias = [];
            $resultado = $stmt->fetchAll(PDO::FETCH_OBJ);
            foreach ($resultado as $familia) {
                $familias[] = new Familia(
                    $familia->id_familia,
                    $familia->nombre,
                    $familia->descripcion,
                    $familia->activo
                );
            }

            // Devolver los productos y la cantidad total de páginas
            return [$familias, $total_paginas];
        } catch (PDOException $e) {
            throw new Exception('Error al obtener productos: ' . $e->getMessage());
        }
    }


    // Borrar una familia por id
    public function borrar_familia($id_familia)
    {
        // Eliminar la familia de la base de datos
        $query = "DELETE FROM familias WHERE id_familia = :id_familia";
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':id_familia', $id_familia, PDO::PARAM_INT);
            if ($stmt->execute()) {
                return "Familia eliminada con éxito.";
            } else {
                return "No se pudo eliminar la familia.";
            }
        } catch (PDOException $e) {
            throw new Exception('Error al eliminar producto: ' . $e->getMessage());
        }
    }

    // Crear una nueva familia
    public function crear_familia(Familia $familia)
    {
        $query = "INSERT INTO familias (nombre, descripcion, activo)
VALUES (:nombre, :descripcion, 1)";
        try {
            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(':nombre', $familia->getNombre());
            $stmt->bindValue(':descripcion', $familia->getDescripcion());
            if ($stmt->execute()) {
                return "Familia añadida con éxito.";
            } else {
                return "Error al crear la familia.";
            }
        } catch (PDOException $e) {
            throw new Exception('Error al crear la familia: ' . $e->getMessage());
        }
    }

    // Editar una familia existente
    public function editar_familia(Familia $familia)
    {
        $query = "UPDATE familias SET nombre = :nombre, descripcion = :descripcion, activo = :activo WHERE id_familia = :id_familia";
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_familia', $familia->getIdFamilia());
            $stmt->bindValue(':nombre', $familia->getNombre());
            $stmt->bindValue(':descripcion', $familia->getDescripcion());
            $stmt->bindValue(':activo', $familia->getActivo());
            if ($stmt->execute()) {
                return "Familia editada con éxito.";
            } else {
                return "Error al editar la familia.";
            }
        } catch (PDOException $e) {
            throw new Exception('Error al editar la familia: ' . $e->getMessage());
        }
    }

    // Obtener familia por id
    public function obtener_familia_id($id_familia)
    {
        $query = 'SELECT * FROM familias WHERE id_familia = :id_familia';

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['id_familia' => $id_familia]);
            $familia = $stmt->fetch(PDO::FETCH_OBJ);
            if ($familia !== false) {
                return new Familia(
                    $familia->id_familia,
                    $familia->nombre,
                    $familia->descripcion,
                    $familia->activo
                );
            }
            return null;
        } catch (PDOException $e) {
            throw new Exception('Error al obtener la familia: ' . $e->getMessage());
        }
    }
}
