<?php
include_once(__DIR__ . '/../config/funciones.php');

// Clase SubFamilia
class Subfamilia
{
    private $id_subfamilia;
    private $id_familia;
    private $nombre;
    private $descripcion;
    private $activo;

    public function __construct($id_subfamilia, $id_familia, $nombre, $descripcion, $activo = 1)
    {
        $this->id_subfamilia = $id_subfamilia;
        $this->id_familia = $id_familia;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->activo = $activo;
    }

    // Métodos getter
    public function getIdSubFamilia()
    {
        return $this->id_subfamilia;
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

// Clase GestorSubFamilias
class GestorSubFamilias
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Mostrar subfamilias con paginación y ordenación
    public function mostrar_subfamilias($buscar = '', $ordenar = 'ASC')
    {
        try {
            // Definir el número de registros por página
            $registros = 10;

            // Obtener el número de página actual
            $pagina = isset($_GET["pagina"]) && is_numeric($_GET["pagina"]) && $_GET["pagina"] > 0 ? (int)$_GET["pagina"] : 1;
            $inicio = ($pagina - 1) * $registros;

            // Calcular el total de registros
            $query_count = "SELECT COUNT(*) as total FROM subfamilias s WHERE s.nombre LIKE :buscar";
            
            $stmt = $this->pdo->prepare($query_count);
            $stmt->bindValue(':buscar', '%' . $buscar . '%');
            $stmt->execute();
            $num_total_registros = $stmt->fetchColumn();

            // Calcular el número total de páginas
            $total_paginas = $num_total_registros > 0 ? ceil($num_total_registros / $registros) : 1;

            // Recuperar los datos de las subfamilias con paginación y ordenación
            $query = "
            SELECT s.*, f.nombre AS nombre_familia
            FROM subfamilias s
            LEFT JOIN familias f ON s.id_familia = f.id_familia
            WHERE s.nombre LIKE :buscar
            ORDER BY s.nombre $ordenar
            LIMIT :inicio, :registros
            ";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':buscar', '%' . $buscar . '%');
            $stmt->bindValue(':inicio', $inicio, PDO::PARAM_INT);
            $stmt->bindValue(':registros', $registros, PDO::PARAM_INT);
            $stmt->execute();

            // Obtener las subfamilias
            $subfamilias = [];
            $resultado = $stmt->fetchAll(PDO::FETCH_OBJ);
            foreach ($resultado as $subfamilia) {
                $subfamilias[] = new Subfamilia(
                    $subfamilia->id_subfamilia,
                    $subfamilia->id_familia,
                    $subfamilia->nombre,           
                    $subfamilia->descripcion,
                    $subfamilia->activo
                );
            }

            return [$subfamilias, $total_paginas];
        } catch (PDOException $e) {
            throw new Exception('Error al obtener subfamilias: ' . $e->getMessage());
        }
    }

    // Borrar una subfamilia por id
    public function borrar_subfamilia($id_subfamilia)
    {
        $query = "DELETE FROM subfamilias WHERE id_subfamilia = :id_subfamilia";
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':id_subfamilia', $id_subfamilia, PDO::PARAM_INT);
            if ($stmt->execute()) {
                return "Subfamilia eliminada con éxito.";
            } else {
                throw new Exception("No se pudo eliminar la subfamilia.");
            }
        } catch (PDOException $e) {
            throw new Exception('Error al eliminar subfamilia: ' . $e->getMessage());
        }
    }


    // Crear una nueva subfamilia
    public function crear_subfamilia(Subfamilia $subfamilia)
    {
        $query = "INSERT INTO subfamilias (id_familia, nombre, descripcion, activo)
                  VALUES (:id_familia, :nombre, :descripcion, 1)";
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_familia', $subfamilia->getIdFamilia());
            $stmt->bindValue(':nombre', $subfamilia->getNombre());
            $stmt->bindValue(':descripcion', $subfamilia->getDescripcion());
            if ($stmt->execute()) {
                return "Subfamilia añadida con éxito.";
            } else {
                return "Error al crear la subfamilia.";
            }
        } catch (PDOException $e) {
            throw new Exception('Error al crear la subfamilia: ' . $e->getMessage());
        }
    }

    // Editar una subfamilia existente
    public function editar_subfamilia(Subfamilia $subfamilia)
    {
        $query = "UPDATE subfamilias SET id_familia = :id_familia, nombre = :nombre, descripcion = :descripcion, activo = :activo WHERE id_subfamilia = :id_subfamilia";
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_familia', $subfamilia->getIdFamilia());
            $stmt->bindValue(':nombre', $subfamilia->getNombre());
            $stmt->bindValue(':descripcion', $subfamilia->getDescripcion());
            $stmt->bindValue(':activo', $subfamilia->getActivo());
            $stmt->bindValue(':id_subfamilia', $subfamilia->getIdSubFamilia());
            if ($stmt->execute()) {
                return "Subfamilia editada con éxito.";
            } else {
                return "Error al editar la subfamilia.";
            }
        } catch (PDOException $e) {
            throw new Exception('Error al editar la subfamilia: ' . $e->getMessage());
        }
    }

    // Obtener subfamilia por id
    public function obtener_subfamilia_id($id_subfamilia)
    {
        $query = 'SELECT * FROM subfamilias WHERE id_subfamilia = :id_subfamilia';

        try {
            $stmt = $this->pdo->prepare($query);
            //Ejecutamos la consulta pasandole los parametros vinculados (id_subfamilia)
            $stmt->execute(['id_subfamilia' => $id_subfamilia]);
            $subfamilia = $stmt->fetch(PDO::FETCH_OBJ);
            if ($subfamilia !== false) {
                return new Subfamilia(
                    $subfamilia->id_subfamilia,
                    $subfamilia->id_familia,
                    $subfamilia->nombre,
                    $subfamilia->descripcion,
                    $subfamilia->activo
                );
            }
            return null;
        } catch (PDOException $e) {
            throw new Exception('Error al obtener la subfamilia: ' . $e->getMessage());
        }
    }
    public function obtener_familias()
    {
        try {
            $query = "SELECT id_familia, nombre FROM familias WHERE activo = 1"; //Familias activas
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
    
            // Obtener todas las familias y devolverlas como objetos
            return $stmt->fetchAll(PDO::FETCH_ASSOC); 
        } catch (PDOException $e) {
            throw new Exception('Error al obtener familias: ' . $e->getMessage());
        }
    }
    
    public function obtener_subfamilias($idFamilia)
    {
        try {
            //Consulta para obtener todas las subfamilias activas filtradas por id_familia
            $query = "SELECT id_subfamilia, id_familia, nombre FROM subfamilias WHERE activo = 1 AND id_familia = :idFamilia"; // Filtrando las subfamilias activas y asociadas a la familia
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':idFamilia', $idFamilia, PDO::PARAM_INT); // Vinculando el id de la familia
            $stmt->execute();
            //Obtener todas las subfamilias y devolverlas como objetos
            return $stmt->fetchAll(PDO::FETCH_OBJ); // Devuelvo los resultados como objetos
        } catch (PDOException $e) {
            throw new Exception('Error al obtener subfamilias: ' . $e->getMessage());
        }
    }
    

}
