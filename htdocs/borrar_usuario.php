<?php
include_once 'gestores/gestor_usuarios.php';
include_once 'config/conectar_db.php';
session_start();

// Verificamos que el usuario esté logueado y tenga el rol adecuado
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'Administrador') {
    // Redirigimos a la página de acceso si no está logueado o no tiene el rol adecuado
    escribir_log("Intento de acceso en zona de eliminación de usuarios por el usuario ". $_SESSION['usuario'],'acceso');
    header("Location: index.php");
    exit;
}
$pdo = conectar_db();
$gestorUsuarios = new GestorUsuarios($pdo);

if (isset($_GET['id'])) {
    $id = trim($_GET['id']);  //eliminar espacios extra

    $usuario = $gestorUsuarios->obtener_usuario_por_id($id);
    $datos_usuario = $usuario->getNombre() . " " . $usuario->getApellidos() . " con DNI: " . $usuario->getDni();
    if (!$usuario) {
        //Usuario no encontrado, redirigir con mensaje de error
        $_SESSION['errores'][] = "Usuario con id $id no encontrado.";
        header("Location: index.php");
        exit();
    }

    // Verificar si el usuario logueado es el mismo que el usuario o si es un administrador
    if ($_SESSION['rol'] !== 'Administrador' && $_SESSION['id'] !== $usuario->getId()) {
        //Si no es administrador y no es el propio usuario, no tiene permisos para eliminar
        escribir_log("Hubo un intento de borrado del usuario  : $datos_usuario  :: Por el usuario " . $_SESSION['usuario'], 'usuarios');
        $_SESSION['errores'][] = "No tiene permisos para eliminar a este usuario.";
        header("Location: index.php");
        exit();
    }

    // Si el formulario fue enviado con la confirmación de eliminación
    if (isset($_GET['confirmar']) && $_GET['confirmar'] === 'true') {
        // Intentar borrar al cliente
        $borrar_usuario = $gestorUsuarios->borrar_usuario($usuario->getId());
        $usuario_DNI = $usuario->getDni();
        if ($borrar_usuario) {
            escribir_log("El usuario: $datos_usuario  fue eliminado por el usuario " . $_SESSION['usuario'], 'usuarios');
            $_SESSION['mensaje'] = "Usuario con DNI:  $usuario_DNI eliminado correctamente.";
        } else {
            escribir_log("Hubo un intento de elimnación del usuario: $datos_usuario  por el usuario " . $_SESSION['usuario'], 'usuarios');
            $_SESSION['errores'][] = "Error al eliminar el usuario con DNI:  $usuario_DNI.";
        }

        // Redirigir dependiendo del rol
        if ($_SESSION['rol'] !== 'Administrador') {
            escribir_log("Intento de acceso en zona de eliminación de usuarios por el usuario". $_SESSION['usuario'],'acceso');
            session_destroy();
            header("Location:index.php");
            exit();
        } else {
            header("Location:mantenimiento_usuarios.php");
            exit();
        }
    }
}
?>

<?php include_once "includes/header.php"; ?>
<!-- Contenedor principal de la página -->
<div class="container-fluid d-flex flex-column min-vh-100">
    <?php require_once 'config/procesa_errores.php'; ?>
    <div class="row flex-grow-1 justify-content-center">
        <!-- Contenido principal -->
        <main class="col-md-8 col-lg-6 p-4 bg-light">
            <div class="container d-flex flex-column justify-content-center align-items-center py-5">
                <!-- Mensaje de confirmación de eliminación -->
                <div class="card shadow-lg w-100" style="max-width: 100%;">
                    <div class="card-header text-white text-center" style="background-color: #6a0ea7;">
                        <h4 class="mb-0">Confirmación de Eliminación</h4>
                    </div>
                    <div class="card-body text-center">
                        <p class="fs-5">
                            <strong class="text-danger h1">¿Está seguro de que desea eliminar al cliente con DNI: <?php echo htmlspecialchars($usuario->getDni()); ?>?</strong>
                        </p>
                        <p class="text-info h3">En "EDITAR CUENTA", puedes deshabilitar la cuenta y si quieres activarla denuevo tan solo ponte en contacto con info@sabalicante.es.</p>
                        <p class="text-muted">Esta acción no se puede deshacer.</p>

                        <!-- Formulario de confirmación -->
                        <form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="mt-4">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($usuario->getId()); ?>">
                            <input type="hidden" name="confirmar" value="true">
                            <div class="d-flex justify-content-around">
                                <button type="submit" class="btn btn-danger px-4">Sí, eliminar</button>
                                <button type="button" class="btn btn-secondary px-4" onclick="history.back()">Volver</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include_once "includes/footer.php"; ?>