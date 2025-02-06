<?php
session_start();
include_once "config/conectar_db.php";
include_once "gestores/gestor_productos.php";
// Verificamos que el usuario esté logueado y tenga el rol adecuado
if (!isset($_SESSION['acceso']) || $_SESSION['rol'] !== 'Administrador' && $_SESSION['rol'] !== 'Empleado') {
    escribir_log("Error al acceder a la zona de 'nueva producto' por falta de permisos ->" . $_SESSION['usuario'], 'zonas');
    // Redirigimos a la página de acceso si no está logueado o no tiene el rol adecuado
    header("Location: index.php");
    exit;
}

$pdo = conectar_db();
$gestorProductos = new GestorProductos($pdo);
$errores = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codigo = strtoupper(trim($_POST['codigo']));
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $subfamilia = trim($_POST['id_subfamilia']);
    $precio = trim($_POST['precio']);
    $descuento = trim($_POST['descuento']);
    $stock = (int) $_POST['stock'];
    // Validar Subfamilia
    if (empty($subfamilia)) {
        $errores[] = "Debe seleccionar una subfamilia.";
    }

    // Validación del código
    if (!preg_match("/^[A-Za-z]{3}[0-9]{1,5}$/", $codigo)) {
        $errores[] = "El código no es válido. Debe contener tres letras seguidas de hasta cinco números.";
    }

    // Verificar si el código ya existe
    $consulta_codigo = "SELECT * FROM productos WHERE codigo = :codigo";
    try {
        $stmt = $pdo->prepare($consulta_codigo);
        $stmt->bindValue(':codigo', $codigo);
        $stmt->execute();
        if ($stmt->fetch()) {
            $errores[] = "El código $codigo ya está registrado.";
        }
    } catch (PDOException $e) {
        $errores[] = "Error al consultar el código: " . $e->getMessage();
    }

    // Validación de la imagen 
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        if ($_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
            $errores[] = "Error al subir la imagen. Código de error: " . $_FILES['imagen']['error'];
        }
        $nombre_imagen_puro = $_FILES['imagen']['name'];
        $extension = pathinfo($nombre_imagen_puro, PATHINFO_EXTENSION);
        // Generar un nombre único para la imagen
        $nombre_imagen = pathinfo($nombre_imagen_puro, PATHINFO_FILENAME) . "_" . rand(100000000, 999999999) . "." . $extension;
        $ruta_imagen = "imagenes/" . $nombre_imagen;
        $temp_imagen = $_FILES['imagen']['tmp_name'];
        $tipo_imagen_valida = $_FILES['imagen']['type'];
        $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif'];
        // Verificar si es una imagen válida
        if (($imagen_info = getimagesize($temp_imagen)) === false) {
            $errores[] = "El archivo no es una imagen válida.";
        }
        // Verificar el tipo de imagen
        if (!in_array($tipo_imagen_valida, $tipos_permitidos)) {
            $errores[] = "Solo se permiten imágenes en formato JPG, JPEG, GIF o PNG. El formato de la imagen es '$tipo_imagen_valida'.";
        }
    }
    // Si no hay errores, guardar producto
    if (empty($errores)) {
        try {
            $nombre_imagen_bd  = isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK
                ? $nombre_imagen
                : 'sin-imagen.jpg';
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                if (!move_uploaded_file($temp_imagen, $ruta_imagen)) {
                    $errores[] = "Error al cargar la imagen.";
                }
            }

            //Crear producto
            $producto = new Producto($codigo, $nombre, $descripcion, $subfamilia, $precio, $nombre_imagen_bd, $descuento, 1, $stock);
            if ($gestorProductos->crear_producto($producto)) {
                $nom_producto = $producto->getNombre();
                escribir_log("Producto : $nom_producto dado de alta con exito por el usuario: " . $_SESSION['usuario'], 'productos');
                $_SESSION['mensaje'] = "Producto registrado correctamente.";
                header('Location: mantenimiento_productos.php');
                exit();
            } else {
                escribir_log("Error al dar el producto : $nom_producto de alta en el sistema por el usuario: " . $_SESSION['usuario'], 'productos');
                $errores[]  = "Error al dar de alta el producto.";
            }
        } catch (PDOException $e) {
            $errores[]  = "Error al insertar los datos: " . $e->getMessage();
        }
    }

    $_SESSION['errores'] = $errores;
}
?>

<?php
//CABECERA
include_once "includes/header.php";
?>

<!-- Contenedor principal de la página -->
<div class="container-fluid d-flex flex-column min-vh-100 bg-light">
    <div class="row flex-grow-1 justify-content-center ">
        <!-- Formulario de registro -->
        <main class="col-md-8 col-lg-6 p-4">
            <h2 class="text-center mb-4">Formulario de Alta Producto</h2>

            <!-- Muestra errores, si los hay -->
            <?php require_once('config/procesa_errores.php'); ?>
            <!-- Formulario -->
            <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" class="mt-4  border border-dark rounded p-4">
                <!-- Código -->
                <div class="mb-3">
                    <label for="codigo" class="form-label">Código:</label>
                    <input type="text" id="codigo" name="codigo" class="form-control" pattern="[A-Za-z]{3}[0-9]{1,5}" title="Debe contener tres letras seguidas de hasta cinco números." maxlength="8" placeholder="Ej: ABC12345" required>
                </div>

                <!-- Nombre -->
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Nombre del artículo" required>
                </div>

                <!-- Descripción -->
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción:</label>
                    <input type="text" id="descripcion" name="descripcion" placeholder="Descripción del artículo" class="form-control" required>
                </div>
                <!-- SubFamilia -->
                <div class="mb-3">
                    <label for="id_subfamilia" class="form-label">Subfamilia:</label>
                    <select id="id_subfamilia" name="id_subfamilia" class="form-control" required>
                        <option value="">Selecciona una subfamilia</option>
                        <?php
                        $subfamilias = $gestorProductos->obtener_subfamilias_visor();
                        if (!empty($subfamilias)) {
                            foreach ($subfamilias as $subfamilia) {
                                $selected = (isset($_POST['id_subfamilia']) && $_POST['id_subfamilia'] == $subfamilia['id_subfamilia']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($subfamilia['id_subfamilia']) . "' $selected>" . htmlspecialchars($subfamilia['nombre']) . "</option>";
                            }
                        } else {
                            echo "<option value=''>No hay subfamilias disponibles</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Precio -->
                <div class="mb-3">
                    <label for="precio" class="form-label">Precio:</label>
                    <input type="number" step="0.01" min="0" placeholder="0.00" id="precio" name="precio" class="form-control" required>
                </div>

                <!-- Imagen -->
                <div class="mb-3">
                    <label for="imagen" class="form-label">Imagen:</label>
                    <input type="file" id="imagen" name="imagen" class="form-control" accept=".jpg,.jpeg,.gif,.png">
                </div>
                <!-- Descuento -->
                <div class="mb-3">
                    <label for="descuento" class="form-label">Descuento:</label>
                    <input type="number" step="0.01" min="0" placeholder="0.00" id="descuento" name="descuento" class="form-control">
                </div>
                <!-- stock -->
                <div class="mb-3">
                    <label for="stock" class="form-label">Stock:</label>
                    <input type="number" step="1" min="0" placeholder="0" id="stock" name="stock" class="form-control">
                </div>
                <!-- FIN Formulario -->
                <!-- Botones -->
                <div class="d-flex justify-content-between mt-4">
                    <button type="submit" class="btn btn-primary" name="agregar_producto">Registrar producto</button>
                    <a href="javascript:history.back()" class="btn btn-secondary">Volver</a>
                </div>
            </form>
        </main>
    </div>
</div>

<!-- Footer -->
<?php include_once "includes/footer.php" ?>