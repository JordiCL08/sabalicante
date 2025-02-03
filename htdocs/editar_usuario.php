<?php
include_once 'gestores/gestor_usuarios.php';
include_once 'config/funciones.php';
include_once 'config/conectar_db.php';
session_start();

// Verifica si el usuario está logueado
if (!isset($_SESSION['acceso']) ) {
    header("Location: index.php");
    exit;
}
$rol_sesion = $_SESSION['rol'];
$pdo = conectar_db();
$id_usuario = $_SESSION['id'];
$gestorUsuario = new GestorUsuarios($pdo);
$errores = [];

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    //Los Usuarios solo pueden editarse ellos mismos
    if ($rol_sesion === 'Usuario' && $id != $id_usuario) {
        header("Location: index.php");
        exit;
    }
    //Los Empleados y contables no pueden editar a un administrador
    if ($rol_sesion === 'Empleado' || $rol_sesion === 'Contable') {
        $usuario = $gestorUsuario->obtener_usuario_por_id($id);
        if ($usuario && $usuario->getRol() === 'Administrador') {
            header("Location: index.php");
            exit;
        }
    }

    // Obtener el usuario a editar
    $usuario = $gestorUsuario->obtener_usuario_por_id($id);
    if (!$usuario) {
        $errores[] = "Usuario no encontrado.";
    }
} else {
    $errores[] = "ID de usuario no proporcionado.";
}
//PROCESAR FORMULARIO PARA EDITAR EL USUARIO O DARLO DE BAJA
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //EDITAR USUARIO
    if (isset($_POST['editar_usuario'])) {
        $id = trim($_POST['id']);
        $dni = trim($_POST['dni']);
        $nombre = trim($_POST['nombre']);
        $apellidos = trim($_POST['apellidos']);
        $direccion = trim($_POST['direccion']);
        $localidad = trim($_POST['localidad']);
        $provincia = trim($_POST['provincia']);
        $cp = trim($_POST['cp']);
        $telefono = trim($_POST['telefono']);
        $email = trim($_POST['email']);
        $clave = trim($_POST['clave']);
        $confirmar_clave = trim($_POST['confirmar_clave']);
        $rol = trim($_POST['rol']);
        $activo = isset($_POST['activo']);

        // Validación de campos obligatorios
        if (empty($dni) || empty($nombre) || empty($apellidos) || empty($direccion) || empty($localidad) || empty($provincia) || empty($cp) || empty($telefono) || empty($email) || empty($rol)) {
            $errores[] = "Todos los campos son obligatorios, excepto la contraseña.";
        }

        // Validar formato del teléfono
        if (!empty($telefono) && !preg_match('/^\d{9}$/', $telefono)) {
            $errores[] = "El teléfono debe tener 9 dígitos.";
        }

        // Validar email
        if (!comprobar_email($email, $errores)) {
            $errores[] = "El email no es válido.";
        }
        // Validar CP
        if (!preg_match('/^\d{5}$/', $cp)) {
            $errores[] = "El código postal debe tener 5 dígitos.";
        }

        // Si se proporcionan nuevas contraseñas
        if (!empty($clave) || !empty($confirmar_clave)) {
            // Verificar que las contraseñas coinciden
            if ($clave !== $confirmar_clave) {
                $errores[] = "Las contraseñas no coinciden.";
            } else {
                // Si coinciden, se encripta la nueva clave
                $clave = password_hash($clave, PASSWORD_DEFAULT);
            }
        } else {
            $clave = $usuario->getClave(); // Asignamos la contraseña actual 
        }

        // Si no hay errores, actualizar usuario
        if (empty($errores)) {
            $usuario->setNombre($nombre);
            $usuario->setApellidos($apellidos);
            $usuario->setDireccion($direccion);
            $usuario->setLocalidad($localidad);
            $usuario->setProvincia($provincia);
            $usuario->setCp($cp);
            $usuario->setTelefono($telefono);
            $usuario->setEmail($email);
            $usuario->setRol($rol);
            $usuario->setClave($clave);
            $usuario->setActivo($activo);
            try {
                $resultado = $gestorUsuario->editar_usuario($usuario);
                if ($resultado) {
                    if ($activo == false) {
                        escribir_log("El usuario $email con DNI: $dni ha sido desactivado por el usuario " . $_SESSION['usuario'], 'usuarios');
                    } else {
                        escribir_log("El usuario $email con DNI: $dni ha sido activado por el usuario " . $_SESSION['usuario'], 'usuarios');
                    }
                    escribir_log("El usuario $email con DNI: $dni ha sido editado por el usuario " . $_SESSION['usuario'], 'usuarios');
                    $_SESSION['mensaje'] = "El usuario ha sido editado.";
                    if ($_SESSION['rol'] === 'Administrador') {
                        header('Location: mantenimiento_usuarios.php');
                        exit();
                    } else {
                        header('Location: centro_usuario.php');
                        exit();
                    }
                }
            } catch (PDOException $e) {
                escribir_log("Error al editar el usuario $email con DNI: $dni por el usuario " . $_SESSION['usuario'], 'usuarios');
                $errores[] = "Error al actualizar datos: " . $e->getMessage();
            }
        }
        $_SESSION['errores'] = $errores;
    }
    //Baja usuario
    if (isset($_POST['dar_baja'])) {
        if ($usuario) {
            if ($rol_sesion === 'Administrador' && $usuario->getId() == $id_usuario) {
                escribir_log("No puedes desactivar tu propia cuenta como administrador : $usuario", 'usuarios');
                $errores[] = "No puedes desactivar tu propia cuenta como administrador.";
            } else {
                try {
                    $usuario->setActivo(false);
                    $resultado = $gestorUsuario->editar_usuario($usuario);
                    if ($resultado) {
                    include_once 'config/logout.php';
                    exit();
                    }
                } catch (PDOException $e) {
                    escribir_log("Error al procesar la baja  del usuario $email con DNI: $dni por el usuario " . $_SESSION['usuario'], 'usuarios');
                    $errores[] = "Error al procesar la baja: " . $e->getMessage();
                }
            }
        } else {
            $errores[] = "Usuario no encontrado.";
        }
        $_SESSION['errores'] = $errores;
    }
}
?>

<?php include_once "includes/header.php"; ?>
<!-- Contenedor principal -->
<div class="container-fluid d-flex flex-column min-vh-100">
    <div class="row flex-grow-1 justify-content-center">
        <!-- Contenido principal -->
        <main class="col-md-8 col-lg-6 p-4 bg-light">
            <h2 class="text-center mb-4">Editar Usuario</h2>
            <!-- Muestra errores, si los hay -->
            <?php require_once 'config/procesa_errores.php'; ?>
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" action="<?php $_SERVER['PHP_SELF']; ?>">
                        <!-- ID ,DNI -->
                        <input type="hidden" name="dni" value="<?php echo htmlspecialchars($usuario->getDni()); ?>">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($usuario->getId()); ?>">
                        <input type="hidden" name="rol" value="<?php echo htmlspecialchars($usuario->getRol()); ?>">
                        <!-- Campos del Usuario -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nombre" class="form-label">Nombre:</label>
                                <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($usuario->getNombre()); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="apellidos" class="form-label">Apellidos:</label>
                                <input type="text" name="apellidos" class="form-control" value="<?php echo htmlspecialchars($usuario->getApellidos()); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="direccion" class="form-label">Dirección:</label>
                                <input type="text" name="direccion" class="form-control" value="<?php echo htmlspecialchars($usuario->getDireccion()); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="localidad" class="form-label">Localidad:</label>
                                <input type="text" name="localidad" class="form-control" value="<?php echo htmlspecialchars($usuario->getLocalidad()); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="provincia" class="form-label">Provincia:</label>
                                <input type="text" name="provincia" class="form-control" value="<?php echo htmlspecialchars($usuario->getProvincia()); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="cp" class="form-label">Código Postal:</label>
                                <input type="text" name="cp" class="form-control" value="<?php echo htmlspecialchars($usuario->getCp()); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="telefono" class="form-label">Teléfono:</label>
                                <input type="tel" name="telefono" class="form-control" value="<?php echo htmlspecialchars($usuario->getTelefono()); ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email:</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($usuario->getEmail()); ?>" required>
                            </div>
                        </div>

                        <!-- Cambio de Rol -->
                        <?php if ($rol_sesion === 'Administrador'): ?>
                            <!-- ROL -->
                            <fieldset class="border p-3">
                                <legend class="w-auto px-2">Selecciona el rol del Usuario:</legend>
                                <!-- USUARIO -->
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="Usuario" name="rol" value="Usuario" <?php echo ($usuario->getRol() == 'Usuario') ? 'checked' : ''; ?> />
                                    <label class="form-check-label" for="Usuario">Usuario</label>
                                </div>
                                <!-- EMPLEADO -->
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="Empleado" name="rol" value="Empleado" <?php echo ($usuario->getRol() == 'Empleado') ? 'checked' : ''; ?> />
                                    <label class="form-check-label" for="Empleado">Empleado</label>
                                </div>
                                <!-- CONTABLE -->
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="Contable" name="rol" value="Contable" <?php echo ($usuario->getRol() == 'Contable') ? 'checked' : ''; ?> />
                                    <label class="form-check-label" for="Contable">Contable</label>
                                </div>
                                <!-- ADMINISTRADOR -->
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="Administrador" name="rol" value="Administrador" <?php echo ($usuario->getRol() == 'Administrador') ? 'checked' : ''; ?> />
                                    <label class="form-check-label" for="Administrador">Administrador</label>
                                </div>
                            </fieldset>
                        <?php endif; ?>

                        <!-- Contraseña -->
                        <div class="row g-3 mt-3">
                            <div class="col-md-6">
                                <label for="clave" class="form-label">Nueva Contraseña:</label>
                                <input type="password" name="clave" class="form-control" placeholder="(Opcional)">
                            </div>
                            <div class="col-md-6">
                                <label for="confirmar_clave" class="form-label">Confirmar Contraseña:</label>
                                <input type="password" name="confirmar_clave" class="form-control" placeholder="(Opcional)">
                            </div>
                        </div>
                        <!-- Activar/Desactivar Cuenta -->
                        <?php if ($rol_sesion === 'Usuario') : ?>
                                <button type="submit" class="btn btn-danger" name="dar_baja" onclick="return confirm('¿Estás seguro de que quieres dar de baja tu cuenta?')">Dar de baja</button>
                        <?php elseif ($rol_sesion !== 'Usuario'): ?>
                            <div class="form-check form-switch mt-4">
                                <!-- Checkbox como interruptor -->
                                <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1"
                                    <?php echo ($usuario->getActivo() ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="activo" id="estadoEtiqueta">
                                    <?php echo ($usuario->getActivo() ? 'Cuenta activada' : 'Cuenta desactivada'); ?>
                                </label>
                            </div>
                            <!-- Script para actualizar el texto dinámicamente -->
                            <script>
                                // Obtener el interruptor y la etiqueta de estado
                                const estado = document.getElementById('activo');
                                const estadoEtiqueta = document.getElementById('estadoEtiqueta');
                                //Aactualiza el texto según el estado del interruptor
                                function actualizaTextoInterruptor() {
                                    if (estado.checked) {
                                        estadoEtiqueta.textContent = 'Cuenta activa'; // Si el interruptor está activado
                                    } else {
                                        estadoEtiqueta.textContent = 'Cuenta desactivada'; // Si el interruptor está desactivado
                                    }
                                }
                                //Llamar a la función inicialmente para establecer el texto  según el  checkbox
                                actualizaTextoInterruptor();
                                //Cambiaambiar el texto cuando el estado del interruptor cambie
                                estado.addEventListener('change', actualizaTextoInterruptor);
                            </script>
                        <?php endif; ?>
                        <!-- Botones -->
                        <div class="d-flex justify-content-between mt-4">
                            <button type="submit" class="btn btn-primary px-4" name="editar_usuario">Guardar Cambios</button>
                            <button type="button" class="btn btn-secondary px-4" onclick="history.back()">Volver</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include_once "includes/footer.php"; ?>