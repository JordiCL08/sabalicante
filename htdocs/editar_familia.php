<?php
include_once "config/conectar_db.php";
include_once "gestores/gestor_familias.php";
session_start();

// Verificamos que el usuario esté logueado y tenga el rol adecuado
if (!isset($_SESSION['acceso']) || ($_SESSION['rol'] !== 'Administrador' && $_SESSION['rol'] !== 'Empleado')) {
    // Redirigimos a la página de acceso si no está logueado o no tiene el rol adecuado
    header("Location: index.php");
    exit;
}

$pdo = conectar_db();
$gestorFamilia = new GestorFamilias($pdo);

$errores = [];
$familia = [];
if (isset($_GET['id_familia'])) {
    $id_familia = $_GET['id_familia']; //Asignamos a la variable el id de la familia desde la sesion
    //Obtenemos la familia por el id desde la funcion obtener familia id
    $familia = $gestorFamilia->obtener_familia_id($id_familia);

    if (!$familia) {
        $errores[] = "Familia no encontrada.";
    }
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar_familia'])) {
    //Recibimos los datos del formulario y los asignamos a la variable
    $id_familia = trim($_POST['id_familia']);
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $activo = trim($_POST['activo']);

    if (empty($nombre)) {
        $errores[] = "El nombre es obligatorio.";
    }
    if (empty($descripcion)) {
        $errores[] = "La descripción es obligatoria.";
    }

    if (empty($errores)) {
        try {
            //Asignamos los nuevos datos 
            $familia = new Familia($id_familia, $nombre, $descripcion, $activo);
            $resultado = $gestorFamilia->editar_familia($familia);
            if ($resultado) {
                if ($activo == false) {
                    escribir_log("Familia con id: $id_familia desactivada por el usuario " . $_SESSION['usuario'], 'familias');
                } else {
                    escribir_log("Familia con id: $id_familia activada por el usuario " . $_SESSION['usuario'], 'familias');
                }
                escribir_log("Familia con id: $id_familia editada por el usuario " . $_SESSION['usuario'], 'familias');
                $_SESSION['mensaje'] = "Familia editada correctamente.";
                header('Location: mantenimiento_familias.php');
                exit();
            }
        } catch (PDOException $e) {
            escribir_log("Error al editar la familia con id: $id_familia por el usuario " . $_SESSION['usuario'], 'familias');
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
<div class="container-fluid d-flex flex-column min-vh-100 bg-light">
    <div class="row flex-grow-1 justify-content-center">
        <!-- Formulario de edición de familia -->
        <main class="col-md-8 col-lg-6 p-4">
            <h2 class="text-center mb-4">Editar Familia</h2>

            <!-- Muestra errores, si los hay -->
            <?php require_once('config/procesa_errores.php'); ?>

            <!-- Formulario -->
            <form method="POST" action="<?php $_SERVER['PHP_SELF']; ?>" class="mt-4  border border-dark rounded p-4">
                <!-- Campo oculto  -->
                <input type="hidden" name="id_familia" value="<?php echo htmlspecialchars($familia->getIdFamilia()); ?>">
                <!-- Nombre -->
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Nombre de la familia" value="<?php echo htmlspecialchars($familia->getNombre()); ?>" required>
                </div>

                <!-- Descripción -->
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción:</label>
                    <input type="text" id="descripcion" name="descripcion" placeholder="Descripción de la familia" class="form-control" value="<?php echo htmlspecialchars($familia->getDescripcion()); ?>" required>
                </div>

                <!-- Activar/Desactivar Familia -->
                <div class="form-check form-switch mt-4">
                    <!-- Checkbox como interruptor -->
                    <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1"
                        <?php echo ($familia->getActivo() ? 'checked' : ''); ?>>
                    <label class="form-check-label" for="activo" id="estadoEtiqueta">
                        <?php echo ($familia->getActivo() ? 'Familia activada' : 'Familia desactivada'); ?>
                    </label>
                </div>

                <!-- Script para actualizar el texto dinámicamente -->
                <script>
                    // Obtener el interruptor y la etiqueta de estado
                    const estado = document.getElementById('activo');
                    const estadoEtiqueta = document.getElementById('estadoEtiqueta');

                    // Función que actualiza el texto según el estado del interruptor
                    function actualizaTextoInterruptor() {
                        if (estado.checked) {
                            estadoEtiqueta.textContent = 'Familia activa'; // Si el interruptor está activado
                        } else {
                            estadoEtiqueta.textContent = 'Familia desactivada'; // Si el interruptor está desactivado
                        }
                    }
                    // Llamar a la función inicialmente para establecer el texto correcto según el estado del checkbox
                    actualizaTextoInterruptor();
                    // Agregar un evento para cambiar el texto cuando el estado del interruptor cambie
                    estado.addEventListener('change', actualizaTextoInterruptor);
                </script>

                <!-- Botones -->
                <div class="d-flex justify-content-between mt-4">
                    <button type="submit" class="btn btn-primary" name="editar_familia">Guardar cambios</button>
                    <a href="javascript:history.back()" class="btn btn-secondary">Volver</a>
                </div>
            </form>
        </main>
    </div>
</div>

<!-- Footer -->
<?php include_once "includes/footer.php" ?>