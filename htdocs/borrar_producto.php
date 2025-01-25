<?php
include_once "config/conectar_db.php";
include_once "gestores/gestor_productos.php";
session_start();

// Verificamos que el usuario esté logueado y tenga el rol adecuado
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'Administrador') {
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

    // Si el código está presente y se confirma la eliminación
    if (isset($_GET['confirmar']) && $_GET['confirmar'] === 'true') {
        try {
            // Llamar a la función para eliminar el producto de la base de datos
            $borrar_producto = $gestorProductos->borrar_producto($codigo); // Asegúrate de pasar solo el código

            if ($borrar_producto) {
                $_SESSION['mensaje'] = "Producto con código $codigo eliminado correctamente.";
            } else {
                $_SESSION['errores'][] = "Error al eliminar el producto con código: $codigo.";
            }

            // Redirigir al índice después de intentar eliminar
            header("Location: mantenimiento_productos.php");
            exit;
        } catch (Exception $e) {
            $_SESSION['errores'][] = "Error al intentar eliminar el producto: " . $e->getMessage();
            header("Location: mantenimiento_productos.php");
            exit;
        }
    }
} else {
    // Si no se proporciona un código, redirigir a la página principal
    $_SESSION['errores'][] = "Código de producto no proporcionado.";
    header("Location: mantenimiento_productos.php");
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
