<?php
session_start();
include_once "config/conectar_db.php";
include_once "gestores/gestor_subfamilias.php";

// Verificamos que el usuario esté logueado y tenga el rol adecuado
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'Administrador' && $_SESSION['rol'] !== 'Empleado') {
    escribir_log("Error al acceder a la zona de 'nueva subfamilia' por falta de permisos ->" . $_SESSION['usuario'], 'zonas');
    // Redirigimos a la página de acceso si no está logueado o no tiene el rol adecuado
    header("Location: index.php");
    exit;
}

$pdo = conectar_db();
$gestorSubFamilia = new GestorSubFamilias($pdo);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_familia = trim($_POST['id_familia']);
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $errores = [];

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

    // Si no hay errores, guardar subfamilia
    if (empty($errores)) {
        try {
            // Crear el objeto Subfamilia
            $subfamilia = new Subfamilia(null, $id_familia, $nombre, $descripcion, 1);
            // Registrar la subfamilia
            if ($gestorSubFamilia->crear_subfamilia($subfamilia)) {
                $nom_subfamilia = $subfamilia->getNombre();
                escribir_log("Subamilia : $nom_subfamilia dada de alta con exito por el usuario: " . $_SESSION['usuario'], 'subfamilias');
                $_SESSION['mensaje'] = "Subfamilia registrada correctamente.";
                header('Location: mantenimiento_subfamilias.php');
                exit();
            } else {
                escribir_log("Error al dar la subfamilia : $nom_subfamilia de alta en el sistema por el usuario: " . $_SESSION['usuario'], 'subfamilias');
                $errores[] = "Error al dar de alta la subfamilia.";
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
<div class="container-fluid d-flex flex-column min-vh-100">
    <div class="row flex-grow-1 justify-content-center">
        <!-- Formulario de registro -->
        <main class="col-md-8 col-lg-6 p-4 bg-light">
            <h2 class="text-center mb-4">Formulario de Alta Subfamilias</h2>

            <!-- Muestra errores, si los hay -->
            <?php require_once('config/procesa_errores.php'); ?>
            <!-- Formulario -->
            <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" class="mt-4">
                <!-- Nombre -->
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Nombre de la subfamilia" required>
                </div>

                <!-- Descripción -->
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción:</label>
                    <input type="text" id="descripcion" name="descripcion" placeholder="Descripción de la subfamilia" class="form-control" required>
                </div>

                <!-- Familia -->
                <div class="mb-3">
                    <label for="id_familia" class="form-label">Familia:</label>
                    <select id="id_familia" name="id_familia" class="form-control" required>
                        <!--Familias disponibles -->
                        <option value="">Selecciona una familia</option>
                        <?php
                        $familias = $gestorSubFamilia->obtener_familias();
                        foreach ($familias as $familia) {
                            echo "<option value='" . $familia['id_familia'] . "'>" . $familia['nombre'] . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Botones -->
                <div class="d-flex justify-content-between mt-4">
                    <button type="submit" class="btn btn-primary" name="agregar_subfamilia">Registrar subfamilia</button>
                    <a href="javascript:history.back()" class="btn btn-secondary">Volver</a>
                </div>
            </form>
        </main>
    </div>
</div>

<!-- Footer -->
<?php include_once "includes/footer.php"; ?>