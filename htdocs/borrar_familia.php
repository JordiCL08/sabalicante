<?php
include_once "config/conectar_db.php";
include_once "gestores/gestor_familias.php";
session_start();

// Verificamos que el usuario esté logueado y tenga el rol adecuado
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'Administrador') {
    // Redirigimos a la página de acceso si no está logueado o no tiene el rol adecuado
    header("Location: index.php");
    exit;
}

$pdo = conectar_db();
$gestorFamilias = new GestorFamilias($pdo);

// Verificar si se ha enviado el código de la familia
if (isset($_GET['id_familia'])) {
    $id_familia = strtoupper(trim($_GET['id_familia'])); // Obtener el código y ponerlo en mayúsculas

    // Obtener  la familia usando el código
    $familia = $gestorFamilias->obtener_familia_id($id_familia);

    if (!$familia) {
        // Si no se encuentra  la familia, redirigir con mensaje de error
        $_SESSION['errores'][] = "Familia con Código: $id_familia no encontrada.";
        header("Location: mantenimiento_familias.php");
        exit();
    }

    $nombre_familia = $familia->getNombre();

    // Si el código está presente y se confirma la eliminación
    if (isset($_GET['confirmar']) && $_GET['confirmar'] === 'true') {
        try {
            // Llamar a la función para eliminar la familia de la base de datos
            $borrar_familia = $gestorFamilias->borrar_familia($id_familia); // Asegúrate de pasar solo el código

            if ($borrar_familia) {
                $_SESSION['mensaje'] = "Familia con id: $id_familia eliminada correctamente.";
            } else {
                $_SESSION['errores'][] = "Error al eliminar la familia: $nombre_familia.";
            }

            // Redirigir al índice después de intentar eliminar
            header("Location: mantenimiento_familias.php");
            exit;
        } catch (Exception $e) {
            $_SESSION['errores'][] = "Error al intentar eliminar la familia: " . $e->getMessage();
            header("Location: mantenimiento_familias.php");
            exit;
        }
    }
} else {
    // Si no se proporciona un código, redirigir a la página principal
    $_SESSION['errores'][] = "Código de familia no proporcionado.";
    header("Location: mantenimiento_familias.php");
    exit;
}
?>

<!-- HTML y confirmación de eliminación -->
<?php include_once "includes/header.php"; ?>
<div class="container-fluid d-flex flex-column min-vh-100">
    <?php require_once 'config/procesa_errores.php'; ?>
    <div class="row flex-grow-1 justify-content-center">
        <main class="col-md-8 col-lg-6 p-4 bg-light">
            <div class="container d-flex flex-column justify-content-center align-items-center py-5">
                <div class="card shadow-lg w-100">
                    <div class="card-header text-white text-center" style="background-color: #6a0ea7;">
                        <h4 class="mb-0">Confirmación de Eliminación</h4>
                    </div>
                    <div class="card-body text-center">
                        <p class="fs-5">
                            <strong class="text-danger">¿Está seguro de que desea eliminar la familia: <?php echo htmlspecialchars($familia->getNombre()); ?>?</strong>
                        </p>
                        <p class="text-muted">Esta acción no se puede deshacer.</p>
                        <!-- Formulario de confirmación -->
                        <form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="mb-4">
                            <input type="hidden" name="id_familia" value="<?php echo htmlspecialchars($familia->getIdFamilia()); ?>">
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
