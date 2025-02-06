<?php
include_once "config/conectar_db.php";
include_once "gestores/gestor_productos.php";
session_start();

// Verificamos que el usuario esté logueado y tenga el rol adecuado
if (!isset($_SESSION['acceso']) || ($_SESSION['rol'] !== 'Administrador' && $_SESSION['rol'] !== 'Empleado')) {
    // Redirigimos a la página de acceso si no está logueado o no tiene el rol adecuado
    header("Location: index.php");
    exit;
}

$pdo = conectar_db();
$gestorProductos = new GestorProductos($pdo);

// Verificar si se ha enviado el código del producto
if (isset($_GET['codigo'])) {
    $codigo = strtoupper(trim($_GET['codigo'])); // Obtener el código y ponerlo en mayúsculas

    // Obtener el producto usando el código
    $producto = $gestorProductos->obtener_producto_codigo($codigo); 

    if (!$producto) {
        // Si no se encuentra el producto, redirigir con mensaje de error
        $_SESSION['errores'][] = "Producto con Código: $codigo no encontrado.";
        header("Location: mantenimiento_productos.php");
        exit();
    }

    //Si el codigo es correcto y se confirma borramos el productos
    if (isset($_GET['confirmar']) && $_GET['confirmar'] === 'true') {
        try {
            // Llamar a la función para eliminar el producto de la base de datos por su código
            $borrar_producto = $gestorProductos->borrar_producto($codigo); 

            if ($borrar_producto) {
                escribir_log("Producto con codigo: $codigo eliminado por el usuario ". $_SESSION['usuario'],'productos');
                $_SESSION['mensaje'] = "Producto con código $codigo eliminado correctamente.";
            } else {
                escribir_log("Hubo un intento de borrado de producto con codigo: $codigo por el usuario". $_SESSION['usuario'],'productos');
                $_SESSION['errores'][] = "Error al eliminar el producto con código: $codigo.";
            }
            //Volvemos a mantenimiento productos
            header("Location: mantenimiento_productos.php");
            exit;
        } catch (Exception $e) {
            $_SESSION['errores'][] = "Error al intentar eliminar el producto: " . $e->getMessage();
            header("Location: mantenimiento_productos.php");
            exit;
        }
    }
} else {
    //Si no obtiene el codigo de un producto da eeror
    $_SESSION['errores'][] = "Código de producto no proporcionado.";
    header("Location: mantenimiento_productos.php");
    exit;
}
?>

<!-- HTML y confirmación de eliminación -->
<?php include_once "includes/header.php"; ?>
<div class="container-fluid d-flex flex-column min-vh-100 bg-light">
    <?php require_once 'config/procesa_errores.php'; ?>
    <div class="row flex-grow-1 justify-content-center">
        <main class="col-md-8 col-lg-6 p-4">
            <div class="container d-flex flex-column justify-content-center align-items-center py-5">
                <div class="card shadow-lg w-100">
                    <div class="card-header text-white text-center" style="background-color: #6a0ea7;">
                        <h4 class="mb-0">Confirmación de Eliminación</h4>
                    </div>
                    <div class="card-body text-center">
                        <p class="fs-5">
                            <strong class="text-danger">¿Está seguro de que desea eliminar el código: <?php echo htmlspecialchars($producto->getCodigo()); ?> del producto: <?php echo htmlspecialchars($producto->getNombre()); ?>?</strong>
                        </p>
                        <p class="text-muted">Esta acción no se puede deshacer.</p>
                        <!-- Formulario de confirmación -->
                        <form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="mb-4">
                            <input type="hidden" name="codigo" value="<?php echo htmlspecialchars($producto->getCodigo()); ?>">
                            <input type="hidden" name="confirmar" value="true">
                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-danger">Sí, eliminar</button>
                                <button type="button" class="btn btn-secondary" onclick="window.location.href='mantenimiento_productos.php'">Volver</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<?php include_once "includes/footer.php"; ?>
