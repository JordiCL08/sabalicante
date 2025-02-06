<?php
session_start();
include_once "config/conectar_db.php";
include_once "gestores/gestor_familias.php";
// Verificamos que el usuario esté logueado y tenga el rol adecuado
if (!isset($_SESSION['acceso']) || $_SESSION['rol'] !== 'Administrador' && $_SESSION['rol'] !== 'Empleado') {
    escribir_log("Error al acceder a la zona de 'nueva familia' por falta de permisos ->" . $_SESSION['usuario'], 'zonas');
    // Redirigimos a la página de acceso si no está logueado o no tiene el rol adecuado
    header("Location: index.php");
    exit;
}
$pdo = conectar_db();
$gestorFamilia = new GestorFamilias($pdo);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $errores = [];

    // Validaciones
    if (empty($nombre)) {
        $errores[] = "El nombre de la familia es obligatorio.";
    }
    if (empty($descripcion)) {
        $errores[] = "La descripción de la familia es obligatoria.";
    }

    // Si no hay errores, guardar familia
    if (empty($errores)) {
        try {
            $familia = new Familia(null, $nombre, $descripcion, 1);
            if ($gestorFamilia->crear_familia($familia)) {
                $nom_familia = $familia->getNombre();
                escribir_log("Familia : $nom_familia dada de alta con exito por el usuario: " . $_SESSION['usuario'], 'familias');
                $_SESSION['mensaje'] = "Familia registrada correctamente.";
                header('Location: mantenimiento_familias.php');
                exit();
            } else {
                escribir_log("Error al dar la amilia : $nom_familia de alta en el sistema por el usuario: " . $_SESSION['usuario'], 'familias');
                $errores[] = "Error al dar de alta la familia.";
            }
        } catch (PDOException $e) {
            $errores[] = "Error al insertar los datos: " . $e->getMessage();
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
        <!-- Formulario de registro -->
        <main class="col-md-8 col-lg-6 p-4">
            <h2 class="text-center mb-4">Formulario de Alta Familias</h2>

            <!-- Muestra errores, si los hay -->
            <?php require_once('config/procesa_errores.php'); ?>
            <!-- Formulario -->
            <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="mt-4  border border-dark rounded p-4">
                <!-- Nombre -->
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Nombre de la familia" required>
                </div>

                <!-- Descripción -->
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción:</label>
                    <input type="text" id="descripcion" name="descripcion" placeholder="Descripción de la familia" class="form-control" required>
                </div>

                <!-- Botones -->
                <div class="d-flex justify-content-between mt-4">
                    <button type="submit" class="btn btn-primary" name="agregar_familia">Registrar familia</button>
                    <a href="javascript:history.back()" class="btn btn-secondary">Volver</a>
                </div>
            </form>
        </main>
    </div>
</div>

<!-- Footer -->
<?php include_once "includes/footer.php"; ?>