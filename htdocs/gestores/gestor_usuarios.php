<?php
include_once(__DIR__ . '/../config/funciones.php');
include_once(__DIR__ . '/../config/conectar_db.php');
class Usuario
{
    private $id;
    private $clave;
    private $dni;
    private $nombre;
    private $apellidos;
    private $direccion;
    private $localidad;
    private $provincia;
    private $cp;
    private $telefono;
    private $email;
    private $rol;
    private $activo;
    //Constructor
    public function __construct($id, $clave, $dni, $nombre, $apellidos, $direccion, $localidad, $provincia, $cp, $telefono, $email, $rol, $activo)
    {
        $this->id = $id;
        $this->clave = $clave;
        $this->dni = $dni;
        $this->nombre = $nombre;
        $this->apellidos = $apellidos;
        $this->direccion = $direccion;
        $this->localidad = $localidad;
        $this->provincia = $provincia;
        $this->cp = $cp;
        $this->telefono = $telefono;
        $this->email = $email;
        $this->rol = $rol;
        $this->activo = $activo;
    }
    // Getters
    public function getId()
    {
        return $this->id;
    }
    public function getClave()
    {
        return $this->clave;
    }
    public function getDni()
    {
        return $this->dni;
    }
    public function getNombre()
    {
        return $this->nombre;
    }
    public function getApellidos()
    {
        return $this->apellidos;
    }
    public function getDireccion()
    {
        return $this->direccion;
    }
    public function getLocalidad()
    {
        return $this->localidad;
    }
    public function getProvincia()
    {
        return $this->provincia;
    }
    public function getCp()
    {
        return $this->cp;
    }
    public function getTelefono()
    {
        return $this->telefono;
    }
    public function getEmail()
    {
        return $this->email;
    }
    public function getRol()
    {
        return $this->rol;
    }
    public function getActivo()
    {
        return $this->activo;
    }

    // Setters
    public function setId($id)
    {
        $this->id = $id;
    }
    public function setClave($clave)
    {
        $this->clave = $clave;
    }
    public function setDni($dni)
    {
        $this->dni = $dni;
    }
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }
    public function setApellidos($apellidos)
    {
        $this->apellidos = $apellidos;
    }
    public function setDireccion($direccion)
    {
        $this->direccion = $direccion;
    }
    public function setLocalidad($localidad)
    {
        $this->localidad = $localidad;
    }
    public function setProvincia($provincia)
    {
        $this->provincia = $provincia;
    }
    public function setCp($cp)
    {
        $this->cp = $cp;
    }
    public function setTelefono($telefono)
    {
        $this->telefono = $telefono;
    }
    public function setEmail($email)
    {
        $this->email = $email;
    }
    public function setRol($rol)
    {
        $this->rol = $rol;
    }
    public function setActivo($activo)
    {
        $this->activo = $activo;
    }
}

class GestorUsuarios
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    //Funcion mostrar usuarios
    public function mostrar_usuarios($buscar = '', $ordenar = 'ASC')
    {
        try {
            if (!in_array(strtoupper($ordenar), ['ASC', 'DESC'])) {
                throw new Exception('Error');
            }
            $registros = 10;
            $pagina = isset($_GET["pagina"]) && is_numeric($_GET["pagina"]) && $_GET["pagina"] > 0 ? (int)$_GET["pagina"] : 1;
            $inicio = ($pagina - 1) * $registros;

            // Si hay un valor de búsqueda, añadimos el WHERE
            if (!empty($buscar)) {
                $query = "SELECT * FROM usuarios WHERE dni LIKE :buscar ORDER BY nombre $ordenar LIMIT :inicio, :registros";
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':buscar', '%' . $buscar . '%', PDO::PARAM_STR);
            } else {
                $query = "SELECT * FROM usuarios ORDER BY nombre $ordenar LIMIT :inicio, :registros";
                $stmt = $this->pdo->prepare($query);
            }

            // Parámetros de paginación
            $stmt->bindParam(':inicio', $inicio, PDO::PARAM_INT);
            $stmt->bindParam(':registros', $registros, PDO::PARAM_INT);
            $stmt->execute();

            $usuarios = [];
            $resultado = $stmt->fetchAll(PDO::FETCH_OBJ);
            foreach ($resultado as $usuario) {
                $usuarios[] = new Usuario(
                    $usuario->id,
                    $usuario->clave,
                    $usuario->dni,
                    $usuario->nombre,
                    $usuario->apellidos,
                    $usuario->direccion,
                    $usuario->localidad,
                    $usuario->provincia,
                    $usuario->cp,
                    $usuario->telefono,
                    $usuario->email,
                    $usuario->rol,
                    $usuario->activo
                );
            }

            // Contar los usuarios para la paginación
            if (!empty($buscar)) {
                $query_contar = "SELECT COUNT(*) FROM usuarios WHERE dni LIKE :buscar";
                $stmt_contar = $this->pdo->prepare($query_contar);
                $stmt_contar->bindValue(':buscar', '%' . $buscar . '%', PDO::PARAM_STR);
            } else {
                $query_contar = "SELECT COUNT(*) FROM usuarios";
                $stmt_contar = $this->pdo->query($query_contar);
            }
            $stmt_contar->execute();
            $num_total_registros = $stmt_contar->fetchColumn();
            $total_paginas = ceil($num_total_registros / $registros);

            return [$usuarios, $total_paginas];
        } catch (PDOException $e) {
            throw new Exception('Error usuarios: ' . $e->getMessage());
        }
    }

    // Borrar Usuario
    public function borrar_usuario($id)
    {
        $query = "DELETE FROM usuarios WHERE id = :id";
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id', $id);
            if ($stmt->execute()) {
                return "Usuario eliminado con exito.";
            } else {
                return "Usuario no eliminado.";
            }
        } catch (PDOException $e) {
            throw new Exception('Error eliminado el usuario: ' . $e->getMessage());
        }
    }

    // Crear usuario
    public function crear_usuario($usuario)
    {
        try {
            $sql = "INSERT INTO usuarios (clave, dni, nombre, apellidos, direccion, localidad, provincia, cp, telefono, email, rol, activo)
                    VALUES (:clave, :dni, :nombre, :apellidos, :direccion, :localidad, :provincia, :cp, :telefono, :email, :rol, :activo)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':clave', $usuario->getClave());
            $stmt->bindValue(':dni', $usuario->getDni());
            $stmt->bindValue(':nombre', $usuario->getNombre());
            $stmt->bindValue(':apellidos', $usuario->getApellidos());
            $stmt->bindValue(':direccion', $usuario->getDireccion());
            $stmt->bindValue(':localidad', $usuario->getLocalidad());
            $stmt->bindValue(':provincia', $usuario->getProvincia());
            $stmt->bindValue(':cp', $usuario->getCp());
            $stmt->bindValue(':telefono', $usuario->getTelefono());
            $stmt->bindValue(':email', $usuario->getEmail());
            $stmt->bindValue(':rol', $usuario->getRol());
            $stmt->bindValue(':activo', $usuario->getActivo());

            if ($stmt->execute()) {
                return "Usuario creado con exito.";
            } else {
                return "Usuario no creado.";
            }
        } catch (PDOException $e) {
            return "Error al insertar: " . $e->getMessage();
        }
    }


    // Editar Usuario
    public function editar_usuario(Usuario $usuario, $nuevaClave = null)
    {
        // Si no se pasa una nueva contraseña, se mantiene la actual
        $clave = $nuevaClave ? $nuevaClave : $usuario->getClave();

        $query = "UPDATE usuarios SET clave = :clave, dni = :dni, nombre = :nombre, apellidos = :apellidos, 
                  direccion = :direccion, localidad = :localidad, provincia = :provincia, cp = :cp, telefono = :telefono, 
                  email = :email, rol = :rol, activo = :activo WHERE id = :id";
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id', $usuario->getId());
            $stmt->bindValue(':clave', $clave);
            $stmt->bindValue(':dni', $usuario->getDni());
            $stmt->bindValue(':nombre', $usuario->getNombre());
            $stmt->bindValue(':apellidos', $usuario->getApellidos());
            $stmt->bindValue(':direccion', $usuario->getDireccion());
            $stmt->bindValue(':localidad', $usuario->getLocalidad());
            $stmt->bindValue(':provincia', $usuario->getProvincia());
            $stmt->bindValue(':cp', $usuario->getCp());
            $stmt->bindValue(':telefono', $usuario->getTelefono());
            $stmt->bindValue(':email', $usuario->getEmail());
            $stmt->bindValue(':rol', $usuario->getRol());
            $stmt->bindValue(':activo', $usuario->getActivo());
            if ($stmt->execute()) {
                return "Usuario actualizado.";
            } else {
                return "Error al actualizar el usuario.";
            }
        } catch (PDOException $e) {
            throw new Exception('Error al actualizar el usuario: ' . $e->getMessage());
        }
    }

    // Obtener usuario por ID
    public function obtener_usuario_por_id($id)
    {
        $query = "SELECT * FROM usuarios WHERE id = :id";
        try {
            $stmt = $this->pdo->prepare($query);
            // Corregir la asignación del parámetro
            $stmt->execute([':id' => $id]);
            $usuario = $stmt->fetch(PDO::FETCH_OBJ);
            if ($usuario !== false) {
                return new Usuario(
                    $usuario->id,
                    $usuario->clave,
                    $usuario->dni,
                    $usuario->nombre,
                    $usuario->apellidos,
                    $usuario->direccion,
                    $usuario->localidad,
                    $usuario->provincia,
                    $usuario->cp,
                    $usuario->telefono,
                    $usuario->email,
                    $usuario->rol,
                    $usuario->activo
                );
            }
            return null;
        } catch (PDOException $e) {
            throw new Exception('Error al obtener el usuario: ' . $e->getMessage());
        }
    }

    //Buscar usuario
    public function buscar_usuario_por_usuario($usuario, $ordenar = 'ASC')
    {
        $ordenar = in_array(strtoupper($ordenar), ['ASC', 'DESC']) ? strtoupper($ordenar) : 'ASC';
        $query = "SELECT * FROM usuarios WHERE usuario LIKE :usuario ORDER BY nombre $ordenar";

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':usuario', '%' . $usuario . '%', PDO::PARAM_STR);
            $stmt->execute();
            $resultado = $stmt->fetchAll(PDO::FETCH_OBJ);
            if ($resultado) {
                $usuarios = [];
                foreach ($resultado as $usuario) {
                    $usuarios[] = new Usuario(
                        $usuario->id,
                        $usuario->clave,
                        $usuario->dni,
                        $usuario->nombre,
                        $usuario->apellidos,
                        $usuario->direccion,
                        $usuario->localidad,
                        $usuario->provincia,
                        $usuario->cp,
                        $usuario->telefono,
                        $usuario->email,
                        $usuario->rol,
                        $usuario->activo
                    );
                }
                return $usuarios;
            }
            return [];
        } catch (PDOException $e) {
            throw new Exception('Error: ' . $e->getMessage());
        }
    }

    public function baja_usuario($id_usuario) {
        $sql = "UPDATE usuarios SET activo = 0 WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
}
