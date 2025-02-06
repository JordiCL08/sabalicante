<?php
include_once(__DIR__ . '/../config/funciones.php');
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
    public function mostrar_familias($buscar = '', $ordenar = 'ASC')
    {
        try {
            //Definir el número de registros por página
            $registros = 10;

            // btener el número de página actual, si no existe asignamos la página 1
            $pagina = isset($_GET["pagina"]) && is_numeric($_GET["pagina"]) && $_GET["pagina"] > 0 ? (int)$_GET["pagina"] : 1;
            $inicio = ($pagina - 1) * $registros;

            // Crear la consulta base con el filtro de búsqueda (si existe)
            $query = "SELECT COUNT(*) FROM familias WHERE nombre LIKE :buscar";
            //Preparamos la consulta
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':buscar', '%' . $buscar . '%');
            $stmt->execute();
            $num_total_registros = $stmt->fetchColumn();
            //Obtenemos el total de páginas (con ceil redondeamos para arriba)
            $total_paginas = ceil($num_total_registros / $registros);
            // Consulta para obtener las familias con el filtro de búsqueda y orden
            $query = "SELECT * FROM familias WHERE nombre LIKE :buscar ORDER BY nombre $ordenar LIMIT :inicio, :registros";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':buscar', '%' . $buscar . '%');
            $stmt->bindValue(':inicio', $inicio, PDO::PARAM_INT);
            $stmt->bindValue(':registros', $registros, PDO::PARAM_INT);
            $stmt->execute();

            // Obtener las familias
            $familias = [];
            //Convertimos en objeto y lo guardamos en array
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
            //Preparamos la consulta
            $stmt = $this->pdo->prepare($query);
            //Vinculamos los parametros, utilizamos param_int porque es un entero
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
        $query = "INSERT INTO familias (nombre, descripcion, activo) VALUES (:nombre, :descripcion, 1)";
        try {
            //Preparamos la consulta
            $stmt = $this->pdo->prepare($query);
            //Viculamos los valores de los parametros con los valores de la familia
            $stmt->bindValue(':nombre', $familia->getNombre());
            $stmt->bindValue(':descripcion', $familia->getDescripcion());
            //Ejecutamos la consulta
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
            //Preparamos la consulta
            $stmt = $this->pdo->prepare($query);
            //Viculamos los valores de los parametros con los valores de la familia
            $stmt->bindValue(':id_familia', $familia->getIdFamilia());
            $stmt->bindValue(':nombre', $familia->getNombre());
            $stmt->bindValue(':descripcion', $familia->getDescripcion());
            $stmt->bindValue(':activo', $familia->getActivo());
            //Ejecutamos la consulta
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
            //Preparamos la consulta
            $stmt = $this->pdo->prepare($query);
            //Ejectuamos pasando el id de la familia 
            $stmt->execute(['id_familia' => $id_familia]);
            //Obtenemos el resultado como objeto
            $familia = $stmt->fetch(PDO::FETCH_OBJ);
            //si hya familia
            if ($familia !== false) {
                //Devolvemos un objeto con la clase
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
