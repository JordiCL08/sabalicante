<?php
include_once "config/conectar_db.php";
include_once "gestores/gestor_productos.php";
session_start();

// Verificamos que el usuario esté logueado y tenga el rol adecuado
if (!isset($_SESSION['acceso']) || $_SESSION['rol'] !== 'Administrador' && $_SESSION['rol'] !== 'Empleado') {
    // Redirigimos a la página de acceso si no está logueado o no tiene el rol adecuado
    header("Location: index.php");
    exit;
}
$pdo = conectar_db();
$gestorProductos = new GestorProductos($pdo);

$errores = [];
$producto = [];
if (isset($_GET['codigo'])) {
    $codigo = $_GET['codigo'];
    $producto = $gestorProductos->obtener_producto_codigo($codigo);

    if (!$producto) {
        $errores[] = "Producto no encontrado.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar_producto'])) {
    // Recuperar datos del formulario
    $codigo = trim($_POST['codigo']);
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $subfamilia = trim($_POST['id_subfamilia']);
    $precio = trim($_POST['precio']);
    $imagen = isset($producto['imagen']) ? $producto['imagen'] : '';    
    /**IMAGEN ********************************/
    // Verificar si se sube una nueva imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        //Obtener el nombre original y la extensión de la imagen
        $nombre_imagen_puro = $_FILES['imagen']['name'];
        $extension = pathinfo($nombre_imagen_puro, PATHINFO_EXTENSION);
        //Generar un nuevo nombre para la imagen con número aleatorio
        $nombre_imagen = pathinfo($nombre_imagen_puro, PATHINFO_FILENAME) . "_" . rand(100000000, 999999999) . "." . $extension;
        //Mover la imagen al directorio "imagenes"
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], "imagenes/" . $nombre_imagen)) {
            $imagen = $nombre_imagen; // Asignar el nuevo nombre de la imagen
        } else {
            escribir_log("Error al subir la imagen del producto: $codigo por el usuario: " . $_SESSION['usuario'], 'productos');
            $errores[] = "Error al subir la imagen.";
        }
    }
    /*******************************************/
    $descuento = trim($_POST['descuento']);
    $activo = isset($_POST['activo']);
    $stock = trim($_POST['stock']);
    // Validación
    if (empty($nombre)) {
        $errores[] = "El nombre es obligatorio.";
    }
    if (empty($subfamilia)) {
        $errores[] = "Debe seleccionar una subfamilia.";
    }
    if (empty($precio)) {
        $errores[] = "El precio es obligatorio.";
    }

    // Si no hay errores, guardar producto
    if (empty($errores)) {
        try {
            // Crear el objeto Producto
            $producto = new Producto($codigo, $nombre, $descripcion, $subfamilia, $precio, $imagen, $descuento, $activo, $stock);
            // Intentar editar el producto
            $resultado = $gestorProductos->editar_producto($producto);
            if ($resultado) {
                if ($activo == false) {
                    escribir_log("Producto con código: $codigo desactivado por el usuario " . $_SESSION['usuario'], 'productos');
                } else {
                    escribir_log("Producto con código: $codigo activado por el usuario " . $_SESSION['usuario'], 'productos');
                }
                escribir_log("Producto con código: $codigo  editado por el usuario " . $_SESSION['usuario'], 'productos');
                $_SESSION['mensaje'] = "Producto editado correctamente.";
                header('Location: mantenimiento_productos.php');
                exit();
            }
        } catch (PDOException $e) {
            escribir_log("Error al editar el prodcuto con código: $codigo por el usuario " . $_SESSION['usuario'], 'productos');
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
        <!-- Formulario de edición de producto -->
        <main class="col-md-8 col-lg-6 p-4">
            <h2 class="text-center mb-4">Editar Producto</h2>

            <!-- Muestra errores, si los hay -->
            <?php require_once('config/procesa_errores.php'); ?>

            <!-- Formulario -->
            <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" class="mt-4 border border-dark rounded p-4">
                <!-- Código -->
                <div class="mb-3">
                    <h4>Código de producto: <?php echo htmlspecialchars($producto->getCodigo()); ?></h4>
                    <input type="hidden" name="codigo" value="<?php echo htmlspecialchars($producto->getCodigo()); ?>">
                </div>

                <!-- Nombre -->
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Nombre del artículo" value="<?php echo htmlspecialchars($producto->getNombre()); ?>" required>
                </div>

                <!-- Descripción -->
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción:</label>
                    <input type="text" id="descripcion" name="descripcion" placeholder="Descripción del artículo" class="form-control" value="<?php echo htmlspecialchars($producto->getDescripcion()); ?>" required>
                </div>

                <!-- SubFamilia -->
                <div class="mb-3">
                    <label for="id_subfamilia" class="form-label">Subfamilia:</label>
                    <select id="id_subfamilia" name="id_subfamilia" class="form-control" required>
                        <!-- SubFamilias disponibles -->
                        <option value="">Selecciona una subfamilia</option>
                        <?php
                        $subfamilias = $gestorProductos->obtener_subfamilias_visor();
                        foreach ($subfamilias as $subfamilia) {
                            $selected = ($subfamilia['id_subfamilia'] == $producto->getIdSubFamilia()) ? 'selected' : '';
                            echo "<option value='" . $subfamilia['id_subfamilia'] . "' $selected>" . $subfamilia['nombre'] . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Precio -->
                <div class="mb-3">
                    <label for="precio" class="form-label">Precio:</label>
                    <input type="number" step="0.01" min="0" placeholder="0.00" id="precio" name="precio" class="form-control" value="<?php echo htmlspecialchars($producto->getPrecio()); ?>" required>
                </div>

                <!-- Imagen -->
                <div class="mb-3">
                    <label for="imagen" class="form-label">Imagen:</label>
                    <input type="file" id="imagen" name="imagen" class="form-control" accept=".jpg,.jpeg,.gif,.png">
                </div>

                <!-- Descuento -->
                <div class="mb-3">
                    <label for="descuento" class="form-label">Descuento:</label>
                    <input type="number" step="0.01" min="0" placeholder="0.00" id="descuento" name="descuento" class="form-control" value="<?php echo htmlspecialchars($producto->getDescuento()); ?>">
                </div>

                <!-- Stock -->
                <div class="mb-3">
                    <label for="stock" class="form-label">Stock:</label>
                    <input type="number" step="1" min="0" placeholder="0" id="stock" name="stock" class="form-control" value="<?php echo htmlspecialchars($producto->getStock()); ?>">
                </div>
                <!-- Activar/Desactivar Producto -->
                <div class="form-check form-switch mt-4">
                    <!-- Checkbox como interruptor -->
                    <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1"
                        <?php echo ($producto->getActivo() ? 'checked' : ''); ?>>
                    <label class="form-check-label" for="activo" id="estadoEtiqueta">
                        <?php echo ($producto->getActivo() ? 'Producto activada' : 'Producto desactivado'); ?>
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
                            estadoEtiqueta.textContent = 'Producto activo'; // Si el interruptor está activado
                        } else {
                            estadoEtiqueta.textContent = 'Producto desactivado'; // Si el interruptor está desactivado
                        }
                    }
                    // Llamar a la función inicialmente para establecer el texto correcto según el estado del checkbox
                    actualizaTextoInterruptor();
                    // Agregar un evento para cambiar el texto cuando el estado del interruptor cambie
                    estado.addEventListener('change', actualizaTextoInterruptor);
                </script>
                <!-- Botones -->
                <div class="d-flex justify-content-between mt-4">
                    <button type="submit" class="btn btn-primary" name="editar_producto">Guardar cambios</button>
                    <a href="javascript:history.back()" class="btn btn-secondary">Volver</a>
                </div>
            </form>
        </main>
    </div>
</div>

<!-- Footer -->
<?php include_once "includes/footer.php" ?>