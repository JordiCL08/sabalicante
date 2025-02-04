<?php
include_once "config/conectar_db.php";
include_once "gestores/gestor_subfamilias.php";
session_start();

// Verificamos que el usuario esté logueado y tenga el rol adecuado
if (!isset($_SESSION['usuario']) || ($_SESSION['rol'] !== 'Administrador' && $_SESSION['rol'] !== 'Empleado')) {
    // Redirigimos a la página de acceso si no está logueado o no tiene el rol adecuado
    header("Location: index.php");
    exit;
}
$pdo = conectar_db();
$gestorSubFamilia = new GestorSubFamilias($pdo);

$errores = [];
$subfamilia = [];
if (isset($_GET['id_subfamilia'])) {
    $id_subfamilia = $_GET['id_subfamilia'];
    $subfamilia = $gestorSubFamilia->obtener_subfamilia_id($id_subfamilia);

    if (!$subfamilia) {
        $errores[] = "Subfamilia no encontrada.";
    }
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar_subfamilia'])) {
    $id_subfamilia = trim($_POST['id_subfamilia']);
    $id_familia = trim($_POST['id_familia']);
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $activo = isset($_POST['activo']);

    // Validaciones
    if (empty($nombre)) {
        $errores[] = "El nombre de la subfamilia es obligatorio.";
    }
    if (empty($descripcion)) {
        $errores[] = "La descripción de la subfamilia es obligatoria.";
    }
    if (empty($id_familia)) {
        $errores[] = "La familia es obligatoria.";
    }

    if (empty($errores)) {
        $subfamilia = new Subfamilia($id_subfamilia,  $id_familia,  $nombre, $descripcion,  $activo);
        try {
            $resultado = $gestorSubFamilia->editar_subfamilia($subfamilia);
            if ($resultado) {
                if ($activo == false) {
                    escribir_log("Subfamilia con id: $id_subfamilia desactivada por el usuario " . $_SESSION['usuario'], 'subfamilias');
                } else {
                    escribir_log("Subfamilia con id: $id_subfamilia activada por el usuario " . $_SESSION['usuario'], 'subfamilias');
                }
                escribir_log("Subfamilia con id: $id_subfamilia editada por el usuario " . $_SESSION['usuario'], 'subfamilias');
                $_SESSION['mensaje'] = "Subfamilia editada correctamente.";
                header('Location: mantenimiento_subfamilias.php');
                exit();
            }
        } catch (PDOException $e) {
            escribir_log("Error al editar la subfamilia con id: $id_subfamilia por el usuario " . $_SESSION['usuario'], 'subfamilias');
            $errores[] = "Error al actualizar los datos: " . $e->getMessage();
        }
    }
    $_SESSION['errores'] = $errores;
}

?>

<?php
// CABECERA
include_once "includes/header.php";
?>

<!-- Contenedor principal de la página -->
<div class="container-fluid d-flex flex-column min-vh-100">
    <div class="row flex-grow-1 justify-content-center">
        <!-- Formulario de edición de subfamilia -->
        <main class="col-md-8 col-lg-6 p-4 bg-light">
            <h2 class="text-center mb-4">Editar Subfamilia</h2>

            <!-- Muestra errores, si los hay -->
            <?php require_once('config/procesa_errores.php'); ?>

            <!-- Formulario -->
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <!-- Campo oculto  -->
                <input type="hidden" name="id_subfamilia" value="<?php echo htmlspecialchars($subfamilia->getIdSubFamilia()); ?>">

                <!-- Nombre -->
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Nombre de la subfamilia" value="<?php echo htmlspecialchars($subfamilia->getNombre()); ?>" required>
                </div>

                <!-- Descripción -->
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción:</label>
                    <input type="text" id="descripcion" name="descripcion" placeholder="Descripción de la subfamilia" class="form-control" value="<?php echo htmlspecialchars($subfamilia->getDescripcion()); ?>" required>
                </div>

                <!-- Familia -->
                <div class="mb-3">
                    <label for="id_familia" class="form-label">Familia:</label>
                    <select id="id_familia" name="id_familia" class="form-control" required>
                        <!-- Familias disponibles -->
                        <option value="">Selecciona una familia</option>
                        <?php
                        $familias = $gestorSubFamilia->obtener_familias();
                        foreach ($familias as $familia) {
                            $selected = ($familia['id_familia'] == $subfamilia->getIdFamilia()) ? 'selected' : '';
                            echo "<option value='" . $familia['id_familia'] . "' $selected>" . $familia['nombre'] . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <!-- Activar/Desactivar Subfamilia -->
                <div class="form-check form-switch mt-4">
                    <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1" <?php echo ($subfamilia->getActivo() ? 'checked' : ''); ?>>
                    <label class="form-check-label" for="activo" id="estadoEtiqueta">
                        <?php echo ($subfamilia->getActivo() ? 'Subfamilia activada' : 'Subfamilia desactivada'); ?>
                    </label>
                </div>

                <!-- Script para actualizar el texto dinámicamente -->
                <script>
                    const estado = document.getElementById('activo');
                    const estadoEtiqueta = document.getElementById('estadoEtiqueta');

                    function actualizaTextoInterruptor() {
                        estadoEtiqueta.textContent = estado.checked ? 'Subfamilia activa' : 'Subfamilia desactivada';
                    }

                    actualizaTextoInterruptor();
                    estado.addEventListener('change', actualizaTextoInterruptor);
                </script>

                <!-- Botones -->
                <div class="d-flex justify-content-between mt-4">
                    <button type="submit" class="btn btn-primary" name="editar_subfamilia">Guardar cambios</button>
                    <a href="javascript:history.back()" class="btn btn-secondary">Volver</a>
                </div>
            </form>
        </main>
    </div>
</div>

<!-- Footer -->
<?php include_once "includes/footer.php" ?>