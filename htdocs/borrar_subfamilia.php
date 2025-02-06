<?php
include_once "config/conectar_db.php";
include_once "gestores/gestor_subfamilias.php";
session_start();

// Verificamos que el usuario esté logueado y tenga el rol adecuado
if (!isset($_SESSION['acceso']) || ($_SESSION['rol'] !== 'Administrador' && $_SESSION['rol'] !== 'Empleado')) {
    // Redirigimos a la página de acceso si no está logueado o no tiene el rol adecuado
    header("Location: index.php");
    exit;
}
$pdo = conectar_db();
$gestorSubFamilia = new GestorSubFamilias($pdo);

//Verificar si se ha enviado el id de la subfamilia
if (isset($_GET['id_subfamilia'])) {
    $id_subfamilia =trim($_GET['id_subfamilia']); // Obtener el id
    //Obtenemos la subfamilia por el id 
    $subfamilia = $gestorSubFamilia->obtener_subfamilia_id($id_subfamilia);

    if (!$subfamilia) {
        // Si no se encuentra  la familia, redirigir con mensaje de error
        $_SESSION['errores'][] = "Subfamilia con id: $id_subfamilia no encontrada.";
        header("Location: mantenimiento_familias.php");
        exit();
    }

    $nombre_subfamilia = $subfamilia->getNombre();

    //Si el código está presente y se confirma la eliminación
    if (isset($_GET['confirmar']) && $_GET['confirmar'] === 'true') {
        try {
            //Llamar a la función para eliminar la Subfamilia de la base de datos
            $borrar_subfamilia = $gestorSubFamilia->borrar_subfamilia($id_subfamilia); 
            if ($borrar_subfamilia) {
                escribir_log("Subfamilia con id: $id_subfamilia eliminada por el usuario ". $_SESSION['usuario'],'subfamilias');
                $_SESSION['mensaje'] = "Subfamilia con id: $id_subfamilia eliminada correctamente.";
            } else {
                escribir_log("Hubo un intento de borrado de subfamilia con id: $id_subfamilia por el usuario ". $_SESSION['usuario'],'subfamilias');
                $_SESSION['errores'][] = "Error al eliminar la familia: $nombre_subfamilia.";
            }

            // Redirigir al índice después de intentar eliminar
            header("Location: mantenimiento_subfamilias.php");
            exit;
        } catch (Exception $e) {
            $_SESSION['errores'][] = "Error al intentar eliminar la subfamilia: " . $e->getMessage();
            header("Location: mantenimiento_subfamilias.php");
            exit;
        }
    }
} else {
    //Si no recibimos el id nos manda al mantenimiento de subfamilias
    $_SESSION['errores'][] = "ID de familia no proporcionado.";
    header("Location: mantenimiento_subfamilias.php");
    exit;
}
?>

<!-- HTML y confirmación de eliminación -->
<?php include_once "includes/header.php"; ?>
<div class="container-fluid d-flex flex-column min-vh-100 bg-light">
    <?php require_once 'config/procesa_errores.php'; ?>
    <div class="row flex-grow-1 justify-content-center">
        <main class="col-md-8 col-lg-6 p-4 ">
            <div class="container d-flex flex-column justify-content-center align-items-center py-5">
                <div class="card shadow-lg w-100">
                    <div class="card-header text-white text-center" style="background-color: #6a0ea7;">
                        <h4 class="mb-0">Confirmación de Eliminación</h4>
                    </div>
                    <div class="card-body text-center">
                        <p class="fs-5">
                            <strong class="text-danger">¿Está seguro de que desea eliminar la subfamilia: <?php echo htmlspecialchars($subfamilia->getNombre()); ?>?</strong>
                        </p>
                        <p class="text-muted">Esta acción no se puede deshacer.</p>
                        <!-- Formulario de confirmación -->
                        <form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="mb-4">
                            <input type="hidden" name="id_subfamilia" value="<?php echo htmlspecialchars($subfamilia->getIdSubFamilia()); ?>">
                            <input type="hidden" name="confirmar" value="true">
                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-danger">Sí, eliminar</button>
                                <button type="button" class="btn btn-secondary" onclick="window.location.href='mantenimiento_familias.php'">Volver</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<?php include_once "includes/footer.php"; ?>
