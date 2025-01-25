<?php
include_once 'includes/header.php';
// Verificamos que el usuario esté logueado y tenga el rol adecuado
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'Administrador' && $_SESSION['rol'] !== 'Empleado') {
    // Redirigimos a la página de acceso si no está logueado o no tiene el rol adecuado
    header("Location: index.php");
    exit;
}
$rol = $_SESSION['rol'];
$nombre_usuario = $_SESSION['usuario'];

$gestorProductos = new GestorProductos($pdo);

$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$ordenar = isset($_GET['ordenar']) ? $_GET['ordenar'] : 'ASC';
list($productos, $total_paginas) = $gestorProductos->mostrar_productos($buscar, $ordenar);

?>
<div class="container-fluid py-5">
    <h1 class="text-center display-4 mb-4">Mantenimiento de Productos</h1>

    <!-- Muestra errores -->
    <?php include_once 'config/procesa_errores.php'; ?>

    <!-- Formulario de búsqueda -->
    <form method="GET" action="mantenimiento_productos.php" class="mb-4">
        <div class="input-group">
            <label for="buscar" class="visually-hidden">Buscar por nombre</label>
            <input type="text" id="buscar" name="buscar" class="form-control"
                placeholder="Introduce nombre para hacer la búsqueda..."
                value="<?php echo htmlspecialchars($buscar); ?>" aria-label="Buscar por nombre">
            <button type="submit" class="btn btn-primary" aria-label="Buscar">
                <i class="bi bi-search"></i> Buscar
            </button>
            <a href="mantenimiento_productos.php" class="btn btn-secondary" aria-label="Limpiar búsqueda">
                <i class="bi bi-x-circle"></i> Limpiar
            </a>
        </div>
    </form>

    <!-- Tabla de productos -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Código</th>
                    <th>Nombre
                        <?php if ($rol === 'Administrador'): ?>
                            <a href="mantenimiento_productos.php?ordenar=ASC&buscar=<?php echo urlencode($buscar); ?>"
                                class="text-decoration-none" aria-label="Ordenar ascendentemente">⬆️</a>
                            <a href="mantenimiento_productos.php?ordenar=DESC&buscar=<?php echo urlencode($buscar); ?>"
                                class="text-decoration-none" aria-label="Ordenar descendentemente">⬇️</a>
                        <?php endif; ?>
                    </th>
                    <th>Descripción</th>
                    <th>Familia</th>
                    <th>Sub-Familia</th>
                    <th>Precio</th>
                    <th>Imagen</th>
                    <th>Descuento</th>
                    <th>Activo</th>
                    <th>Stock</th>
                    <th>Editar</th>
                    <th>Borrar</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($productos)): ?>
                    <?php foreach ($productos as $producto): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($producto->getCodigo()); ?></td>
                            <td><?php echo htmlspecialchars($producto->getNombre()); ?></td>
                            <td><?php echo htmlspecialchars($producto->getDescripcion()); ?></td>
                            <td><?php echo htmlspecialchars($gestorProductos->obtener_familias($producto->getCodigo())['familia_nombre'] ?? "Sin familia"); ?></td>
                            <td><?php echo htmlspecialchars($gestorProductos->obtener_subfamilias($producto->getCodigo())['subfamilia_nombre'] ?? "Sin subfamilia"); ?></td>
                            <td><?php echo number_format($producto->getPrecio(), 2); ?> €</td>
                            <td>
                                <?php if (htmlspecialchars($producto->getImagen())): ?>
                                    <img src="<?php echo "imagenes/" . htmlspecialchars($producto->getImagen()); ?>"
                                        alt="Imagen de <?php echo htmlspecialchars($producto->getNombre()); ?>"
                                        style="width: 60px; height: auto;">
                                <?php else: ?>
                                    Sin imagen
                                <?php endif; ?>
                            </td>
                            <td><?php echo is_null($producto->getDescuento()) ? 'Sin descuento' : htmlspecialchars($producto->getDescuento()); ?>%</td>
                            <td><?php echo $producto->getActivo() ? 'Sí' : 'No'; ?></td>
                            <td><?php echo htmlspecialchars($producto->getStock()); ?></td>
                            <td>
                                <a href="editar_producto.php?codigo=<?php echo urlencode($producto->getCodigo()); ?>"
                                    class="btn btn-success btn-sm" aria-label="Editar producto">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                            </td>
                            <td>
                                <a href="borrar_producto.php?codigo=<?php echo urlencode($producto->getCodigo()); ?>"
                                    class="btn btn-danger btn-sm" aria-label="Borrar producto">
                                    <i class="bi bi-trash"></i> Borrar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="12" class="text-center">No se encontraron productos.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <?php if (empty($buscar) && $total_paginas > 1): ?>
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <li class="page-item <?php echo ($pagina == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $i; ?>&buscar=<?php echo urlencode($buscar); ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>

    <!-- Botón para nuevo producto -->
    <?php if ($rol === 'Administrador' || $rol === 'Editor'): ?>
        <div class="text-center mt-4">
            <button type="button" onclick="window.location.href='nuevo_producto.php'"
                class="btn btn-success btn-lg" aria-label="Nuevo producto">
                <i class="bi bi-plus-circle"></i> Nuevo producto
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- Footer -->
<?php include_once 'includes/footer.php'; ?>