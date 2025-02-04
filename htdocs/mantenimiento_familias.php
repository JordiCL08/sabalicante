<?php
session_start();
include_once 'config/funciones.php';
// Verificamos que el usuario esté logueado y tenga el rol adecuado
if (!isset($_SESSION['usuario']) || ($_SESSION['rol'] !== 'Administrador' && $_SESSION['rol'] !== 'Empleado')) {
    escribir_log("Error al acceder a la zona de 'Mantenimiento Familias' por falta de permisos ->" . $_SESSION['usuario'], 'zonas');
    // Redirigimos a la página de acceso si no está logueado o no tiene el rol adecuado
    header("Location: index.php");
    exit;
}

include_once 'includes/header.php';

$rol = $_SESSION['rol'];
$nombre_usuario = $_SESSION['usuario'];

$gestorFamilias = new GestorFamilias($pdo);
$buscar_familia = isset($_GET['buscar_familia']) ? trim($_GET['buscar_familia']) : '';
$ordenar = isset($_GET['ordenar']) ? $_GET['ordenar'] : 'ASC';
$pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int)$_GET['pagina'] : 1;

list($familias, $total_paginas) = $gestorFamilias->mostrar_familias($buscar_familia, $ordenar);
?>
<div class="container-fluid py-5">
    <h1 class="text-center display-4 mb-4">Gestión de Familias</h1>

    <!-- Mensajes -->
    <?php include_once 'config/procesa_errores.php'; ?>

    <!-- Formulario de búsqueda -->
    <form method="GET" action="mantenimiento_familias.php" class="mb-4">
        <div class="input-group">
            <input type="text" id="buscar_familia" name="buscar_familia" class="form-control" placeholder="Buscar familia por nombre..."
                value="<?php echo htmlspecialchars($buscar_familia); ?>" aria-label="Buscar Familia">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-search"></i> Buscar
            </button>
            <a href="mantenimiento_familias.php" class="btn btn-secondary">
                <i class="bi bi-x-circle"></i> Limpiar
            </a>
        </div>
    </form>

    <!-- Tabla de familias -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Activo</th>
                    <th>Editar</th>
                    <th>Borrar</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($familias)): ?>
                    <?php foreach ($familias as $familia): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($familia->getIdFamilia()); ?></td>
                            <td><?php echo htmlspecialchars($familia->getNombre()); ?></td>
                            <td><?php echo htmlspecialchars($familia->getDescripcion()); ?></td>
                            <td><?php echo $familia->getActivo() ? 'Sí' : 'No'; ?></td>
                            <td>
                                <a href="editar_familia.php?id_familia=<?php echo urlencode($familia->getIdFamilia()); ?>"
                                    class="btn btn-success btn-sm" aria-label="Editar familia">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                            </td>
                            <td>
                                <a href="borrar_familia.php?id_familia=<?php echo urlencode($familia->getIdFamilia()); ?>"
                                    class="btn btn-danger btn-sm" aria-label="Borrar familia">
                                    <i class="bi bi-trash"></i> Borrar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No se encontraron familias.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <?php if (empty($buscar_familia) && $total_paginas > 1): ?>
        <nav>
            <ul class="pagination justify-content-center pagination-lg">
                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <li class="page-item <?php echo ($pagina == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $i; ?>&buscar_familia=<?php echo urlencode($buscar_familia); ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>

    <!-- Botón para nueva familia -->
    <div class="text-center mt-4">
        <button type="button" onclick="window.location.href='nueva_familia.php'" class="btn btn-success btn-lg" aria-label="Nueva Familia">
            <i class="bi bi-plus-circle"></i> Nueva Familia
        </button>
    </div>
</div>


<!-- Footer -->
<?php include_once 'includes/footer.php'; ?>