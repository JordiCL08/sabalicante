<!-- CABECERA -->
<?php
include_once 'includes/header.php';

// Verificamos que el usuario esté logueado y tenga el rol adecuado
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'Administrador') {
    // Redirigimos a la página de acceso si no está logueado o no tiene el rol adecuado
    header("Location: index.php");
    exit;
}
$rol = $_SESSION['rol'];
$nombre_usuario = $_SESSION['usuario'];

$gestorSubFamilia = new GestorSubFamilias($pdo);
$buscar_subfamilia = isset($_GET['buscar_subfamilia']) ? trim($_GET['buscar_subfamilia']) : '';
$ordenar = isset($_GET['ordenar']) ? $_GET['ordenar'] : 'ASC';
$pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
list($subfamilias, $total_paginas) = $gestorSubFamilia->mostrar_subfamilias($buscar_subfamilia, $ordenar);
?>
<div class="container-fluid py-5">
    <h1 class="text-center display-4 mb-4">Mantenimiento de Subfamilias</h1>

    <!-- Muestra errores -->
    <?php include_once 'config/procesa_errores.php'; ?>

    <!-- Formulario de búsqueda -->
    <form method="GET" action="mantenimiento_subfamilias.php" class="mb-4">
        <div class="input-group">
            <label for="buscar_subfamilia" class="visually-hidden">Buscar por nombre</label>
            <input type="text" id="buscar_subfamilia" name="buscar_subfamilia" class="form-control"
                placeholder="Introduce nombre para hacer la búsqueda..."
                value="<?php echo htmlspecialchars($buscar_subfamilia); ?>" aria-label="Buscar por nombre">
            <button type="submit" class="btn btn-primary" aria-label="Buscar">
                <i class="bi bi-search"></i> Buscar
            </button>
            <a href="mantenimiento_subfamilias.php" class="btn btn-secondary" aria-label="Limpiar búsqueda">
                <i class="bi bi-x-circle"></i> Limpiar
            </a>
        </div>
    </form>

    <!-- Tabla de subfamilias -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre
                        <?php if ($rol === 'Administrador'): ?>
                            <a href="mantenimiento_subfamilias.php?ordenar=ASC&buscar_subfamilia=<?php echo urlencode($buscar_subfamilia); ?>"
                                class="text-decoration-none" aria-label="Ordenar ascendentemente">⬆️</a>
                            <a href="mantenimiento_subfamilias.php?ordenar=DESC&buscar_subfamilia=<?php echo urlencode($buscar_subfamilia); ?>"
                                class="text-decoration-none" aria-label="Ordenar descendentemente">⬇️</a>
                        <?php endif; ?>
                    </th>
                    <th>Descripción</th>
                    <th>Familia</th>
                    <th>Activo</th>
                    <th>Editar</th>
                    <th>Borrar</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($subfamilias)): ?>
                    <?php foreach ($subfamilias as $subfamilia): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($subfamilia->getIdSubFamilia()); ?></td>
                            <td><?php echo htmlspecialchars($subfamilia->getNombre()); ?></td>
                            <td><?php echo htmlspecialchars($subfamilia->getDescripcion()); ?></td>
                            <td>
                                <?php
                                $familias = $gestorSubFamilia->obtener_familias();
                                foreach ($familias as $familia) {
                                    if ($familia['id_familia'] == $subfamilia->getIdFamilia()) {
                                        echo htmlspecialchars($familia['nombre']);
                                    }
                                }
                                ?>
                            </td>
                            <td><?php echo $subfamilia->getActivo() ? 'Sí' : 'No'; ?></td>
                            <td>
                                <a href="editar_subfamilia.php?id_subfamilia=<?php echo urlencode($subfamilia->getIdSubFamilia()); ?>"
                                    class="btn btn-success btn-sm" aria-label="Editar subfamilia">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                            </td>
                            <td>
                                <a href="borrar_subfamilia.php?id_subfamilia=<?php echo urlencode($subfamilia->getIdSubFamilia()); ?>"
                                    class="btn btn-danger btn-sm" aria-label="Borrar subfamilia">
                                    <i class="bi bi-trash"></i> Borrar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No se encontraron subfamilias.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <?php if (empty($buscar_subfamilia) && $total_paginas > 1): ?>
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <li class="page-item <?php echo ($pagina == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $i; ?>&buscar_subfamilia=<?php echo urlencode($buscar_subfamilia); ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>

    <!-- Botón para nueva subfamilia -->
    <?php if ($rol === 'Administrador' || $rol === 'Editor'): ?>
        <div class="text-center mt-4">
            <button type="button" onclick="window.location.href='nueva_subfamilia.php'"
                class="btn btn-success btn-lg" aria-label="Nueva subfamilia">
                <i class="bi bi-plus-circle"></i> Nueva subfamilia
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- Footer -->
<?php include_once 'includes/footer.php'; ?>