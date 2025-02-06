<?php
session_start();
include_once(__DIR__ . '/config/conectar_db.php');
include_once 'gestores/gestor_pedidos.php';
include_once 'gestores/gestor_usuarios.php';

// Verificamos que el usuario esté logueado y tenga el rol adecuado
if (!isset($_SESSION['acceso']) || $_SESSION['rol'] !== 'Administrador' && $_SESSION['rol'] !== 'Empleado' && $_SESSION['rol'] !== 'Contable') {
    escribir_log("Error al acceder a la zona de 'Mantenimiento Pedidos' por falta de permisos ->" . $_SESSION['usuario'], 'zonas');
    // Redirigimos a la página de acceso si no está logueado o no tiene el rol adecuado
    header("Location: index.php");
    exit;
}
$pdo = conectar_db();

$gestorPedidos = new GestorPedidos($pdo);
$gestorUsuarios = new GestorUsuarios($pdo);

// Procesar eliminación de pedido
if (isset($_GET['borrar'])) {
    $id_pedido = $_GET['borrar']; //Obtenemos el id del pedido desde la url al darle a borrar
    //Llamamos al metodo para eliminar el pedido
    $gestorPedidos->eliminar_pedido($id_pedido);
    header("Location: mantenimiento_pedidos.php?mensaje=pedido_eliminado");
    escribir_log("Pedido con ID: $id_pedido ha sido eliminado por el usuario: " . $_SESSION['usuario'], 'pedidos');
    exit;
}

// Procesar cambio de estado del pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pedido'], $_POST['estado'])) {
    $id_pedido = $_POST['id_pedido'];
    $estado = $_POST['estado'];
    //Comprobamos que el estado tenga uno de estos valores
    if (in_array($estado, ['Pendiente', 'Pagado', 'Enviado', 'Cancelado', 'Entregado'])) {
        //Si es correcto, actualizamos el estado en la base de datos
        $gestorPedidos->actualizar_estado_pedido($id_pedido, $estado);
        header("Location: mantenimiento_pedidos.php?mensaje=estado_actualizado");
        escribir_log("Estado del pedido con ID: $id_pedido ha sido actualizado por el usuario: " . $_SESSION['usuario'] . " al estado: $estado.", 'pedidos');
        exit;
    }
}
include_once 'includes/header.php';
$ordenar = isset($_GET['ordenar']) ? $_GET['ordenar'] : 'ASC';
$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
list($pedidos, $total_paginas) = $gestorPedidos->mostrar_pedidos($ordenar, $buscar, $pagina);
?>

<div class="container-fluid py-5">
    <h1 class="text-center display-4 mb-4">Gestión de Pedidos</h1>

    <!-- Mensajes -->
    <?php include_once 'config/procesa_errores.php'; ?>

    <!-- Formulario de búsqueda -->
    <form method="GET" action="mantenimiento_pedidos.php" class="mb-4">
        <div class="input-group">
            <input type="text" id="buscar" name="buscar" class="form-control" placeholder="Buscar pedido por email..."
                value="<?php echo htmlspecialchars($buscar); ?>" aria-label="Buscar por Pedido">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-search"></i> Buscar
            </button>
            <a href="mantenimiento_pedidos.php" class="btn btn-secondary">
                <i class="bi bi-x-circle"></i> Limpiar
            </a>
        </div>
    </form>

    <!-- Tabla de pedidos -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID Pedido</th>
                    <th>Usuario
                        <a href="?ordenar=ASC&buscar=<?php echo urlencode($buscar); ?>" class="text-decoration-none" aria-label="Ordenar ascendentemente">⬆️</a>
                        <a href="?ordenar=DESC&buscar=<?php echo urlencode($buscar); ?>" class="text-decoration-none" aria-label="Ordenar descendentemente">⬇️</a>
                    </th>
                    <th>Fecha Pedido</th>
                    <th>Estado</th>
                    <th>Precio Total</th>
                    <th>Cambiar Estado</th>
                    <th>Envio o Recogida</th>
                    <th>Borrar</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($pedidos)): ?>
                    <?php foreach ($pedidos as $pedido): ?>
                        <?php $usuario = $gestorUsuarios->obtener_usuario_por_id($pedido->id_usuario); ?>
                        <tr>
                            <td><?php echo htmlspecialchars($pedido->id_pedido); ?></td>
                            <td><?php echo htmlspecialchars($usuario->getEmail()); ?></td>
                            <td><?php echo htmlspecialchars($pedido->fecha); ?></td>
                            <td><?php echo htmlspecialchars($pedido->estado); ?></td>
                            <td><?php echo number_format($pedido->total, 2); ?> €</td>
                            <td>
                                <form method="POST" action="mantenimiento_pedidos.php">
                                    <input type="hidden" name="id_pedido" value="<?php echo $pedido->id_pedido; ?>">
                                    <select name="estado" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="Pendiente" <?php echo ($pedido->estado === 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                        <option value="Pagado" <?php echo ($pedido->estado === 'Pagado') ? 'selected' : ''; ?>>Pagado</option>
                                        <option value="Enviado" <?php echo ($pedido->estado === 'Enviado') ? 'selected' : ''; ?>>Enviado</option>
                                        <option value="Cancelado" <?php echo ($pedido->estado === 'Cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                                        <option value="Entregado" <?php echo ($pedido->estado === 'Entregado') ? 'selected' : ''; ?>>Entregado</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($pedido->recogida_local ? 'Recogida en Local' : 'A Domicilio'); ?>
                            </td>
                            <td>
                                <a href="mantenimiento_pedidos.php?borrar=<?php echo urlencode($pedido->id_pedido); ?>"
                                    class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que deseas borrar este pedido?');"><i class="bi bi-trash"></i> Borrar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No se encontraron pedidos.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <!-- Paginación -->
    <?php if (empty($buscar) && $total_paginas > 1): ?>
        <nav>
            <ul class="pagination justify-content-center pagination-lg">
                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <li class="page-item <?php echo ($pagina == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $i; ?>&buscar=<?php echo urlencode($buscar); ?>&ordenar=<?php echo $ordenar; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<!-- Footer -->
<?php include_once 'includes/footer.php'; ?>