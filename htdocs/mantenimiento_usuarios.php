<?php
session_start();
include_once 'config/funciones.php';
// Verificamos que el usuario esté logueado y tenga el rol adecuado
if (!isset($_SESSION['acceso']) || $_SESSION['rol'] !== 'Administrador' && $_SESSION['rol'] !== 'Empleado') {
    escribir_log("Error al acceder a la zona de 'Mantenimiento Usuarios' por falta de permisos ->" . $_SESSION['usuario'], 'zonas');
    // Redirigimos a la página de acceso si no está logueado o no tiene el rol adecuado
    header("Location: index.php");
    exit;
}
include_once "includes/header.php";

$rol = $_SESSION['rol']; //asignamos a la variable rol el rol del usuario de la sesion.
$nombre_usuario = $_SESSION['usuario']; //asignamos a la variable nombre_usuario el email de la sesion.
$gestorUsuarios = new GestorUsuarios($pdo);
$buscar = isset($_POST['dni']) ? $_POST['dni'] : '';
$ordenar = isset($_GET['ordenar']) ? $_GET['ordenar'] : 'ASC';
//Recuperar los usuarios según el buscador y la ordenación
list($usuarios, $total_paginas) = $gestorUsuarios->mostrar_usuarios($buscar, $ordenar);
?>
<div class="container-fluid py-5">
    <h1 class="text-center display-4 mb-4">Mantenimiento de Usuarios</h1>

    <!-- Muestra errores -->
    <?php include_once 'config/procesa_errores.php'; ?>

    <!-- Formulario de búsqueda -->
    <form method="POST" action="mantenimiento_usuarios.php" class="mb-4">
        <div class="input-group">
            <label for="buscar" class="visually-hidden">Buscar por DNI</label>
            <input type="text" id="buscar" name="dni" class="form-control"
                placeholder="Introduce DNI para hacer la búsqueda..."
                value="<?php echo htmlspecialchars($buscar); ?>" aria-label="Buscar por DNI">
            <button type="submit" class="btn btn-primary" aria-label="Buscar">
                <i class="bi bi-search"></i> Buscar
            </button>
            <a href="mantenimiento_usuarios.php" class="btn btn-secondary" aria-label="Limpiar búsqueda">
                <i class="bi bi-x-circle"></i> Limpiar
            </a>
        </div>
    </form>
    <!-- Tabla de usuarios -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>DNI</th>
                    <th>Nombre
                        <a href="mantenimiento_usuarios.php?ordenar=ASC&buscar=<?php echo urlencode($buscar); ?>"
                            class="text-decoration-none" aria-label="Ordenar ascendentemente">⬆️</a>
                        <a href="mantenimiento_usuarios.php?ordenar=DESC&buscar=<?php echo urlencode($buscar); ?>"
                            class="text-decoration-none" aria-label="Ordenar descendentemente">⬇️</a>
                    </th>
                    <th>Apellidos</th>
                    <th>Dirección</th>
                    <th>Localidad</th>
                    <th>Provincia</th>
                    <th>Código Postal</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Activo</th>
                    <th>Editar</th>
                    <th>Borrar</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($usuarios)): ?>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($usuario->getId()); ?></td>
                            <td><?php echo htmlspecialchars($usuario->getDni()); ?></td>
                            <td><?php echo htmlspecialchars($usuario->getNombre()); ?></td>
                            <td><?php echo htmlspecialchars($usuario->getApellidos()); ?></td>
                            <td><?php echo htmlspecialchars($usuario->getDireccion()); ?></td>
                            <td><?php echo htmlspecialchars($usuario->getLocalidad()); ?></td>
                            <td><?php echo htmlspecialchars($usuario->getProvincia()); ?></td>
                            <td><?php echo htmlspecialchars($usuario->getCp()); ?></td>
                            <td><?php echo htmlspecialchars($usuario->getTelefono()); ?></td>
                            <td><?php echo htmlspecialchars($usuario->getEmail()); ?></td>
                            <td><?php echo htmlspecialchars($usuario->getRol()); ?></td>
                            <td><?php echo $usuario->getActivo() ? 'Sí' : 'No'; ?></td>
                            <?php if ($rol === 'Administrador' && $usuario->getEmail() !== $nombre_usuario) : ?>
                                <td>
                                    <a href="editar_usuario.php?id=<?php echo urlencode($usuario->getId()); ?>"
                                        class="btn btn-success btn-sm" aria-label="Editar usuario">
                                        <i class="bi bi-pencil"></i> Editar
                                    </a>
                                </td>
                                <td>
                                    <a href="borrar_usuario.php?id=<?php echo urlencode($usuario->getId()); ?>"
                                        class="btn btn-danger btn-sm" aria-label="Borrar usuario">
                                        <i class="bi bi-trash"></i> Borrar
                                    </a>
                                </td>
                            <?php elseif ($rol === 'Empleado' && $usuario->getRol() === 'Usuario') : ?>
                                <td>
                                    <a href="editar_usuario.php?id=<?php echo urlencode($usuario->getId()); ?>"
                                        class="btn btn-success btn-sm" aria-label="Editar usuario">
                                        <i class="bi bi-pencil"></i> Editar
                                    </a>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>

                    <tr>
                        <td colspan="14" class="text-center">No se encontraron usuarios.</td>
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
    <!-- Botón para nuevo usuario -->
        <div class="text-center mt-4">
            <button type="button" onclick="window.location.href='nuevo_usuario.php'"
                class="btn btn-success btn-lg" aria-label="Nuevo usuario">
                <i class="bi bi-plus-circle"></i> Nuevo usuario
            </button>
        </div>
</div>

<!-- Footer -->
<?php include_once "includes/footer.php"; ?>