<?php
session_start();

include_once 'config/conectar_db.php';
$pdo = conectar_db();

if (!isset($_SESSION['usuario']) || $_SESSION['acceso'] !== true || $_SESSION['rol'] !== 'Usuario') {
    header('Location: index.php');
    exit;
}

$ID_usuario = $_SESSION['id'];

include_once 'gestores/gestor_pedidos.php';
include_once 'gestores/gestor_usuarios.php';

$gestorPedidos = new GestorPedidos($pdo);
$pedidos = $gestorPedidos->obtener_pedido_usuario($ID_usuario);

$gestorUsuarios = new GestorUsuarios($pdo);
$usuarioDetalles = $gestorUsuarios->obtener_usuario_por_id($ID_usuario);


//procesa la cancelacion del pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar_pedido'])) {
    $id_pedido = $_POST['id_pedido'];
    $estado = $_POST['estado'];  // "cancelado"
    $gestorPedidos->actualizar_estado_pedido($id_pedido, $estado);
    header("Location: centro_usuario.php");
    exit;
}

?>
<?php include_once "includes/header.php"; ?>

<div class="container-fluid d-flex flex-column min-vh-100 bg-light">
    <div class="row flex-grow-1 justify-content-center align-items-center py-5">
        <main class="col-12 col-md-10 col-lg-8 p-4 bg-white rounded-3 shadow-lg">
            <?php include_once "config/procesa_errores.php"; ?>
            <div class="text-center mb-5">
                <h2 class="mb-3 text-primary">Bienvenid@, <?php echo htmlspecialchars($usuarioDetalles->getNombre() . " " . $usuarioDetalles->getApellidos()); ?>!</h2>
                <p class="text-muted">Gestiona tu cuenta y consulta tus pedidos.</p>
            </div>

            <div class="row g-4">
                <!-- Información de la cuenta del usuario -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white text-center">
                            <h5 class="mb-0">Detalles de tu cuenta</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><strong>DNI:</strong> <?php echo htmlspecialchars($usuarioDetalles->getDni()); ?></li>
                                <li class="list-group-item"><strong>Nombre:</strong> <?php echo htmlspecialchars($usuarioDetalles->getNombre()); ?></li>
                                <li class="list-group-item"><strong>Apellidos:</strong> <?php echo htmlspecialchars($usuarioDetalles->getApellidos()); ?></li>
                                <li class="list-group-item"><strong>Email:</strong> <?php echo htmlspecialchars($usuarioDetalles->getEmail()); ?></li>
                                <li class="list-group-item"><strong>Dirección:</strong> <?php echo htmlspecialchars($usuarioDetalles->getDireccion()); ?></li>
                                <li class="list-group-item"><strong>Teléfono:</strong> <?php echo htmlspecialchars($usuarioDetalles->getTelefono()); ?></li>
                            </ul>
                            <hr class="hr"/>
                            <div class="d-flex justify-content-between mt-4">
                                <a href="editar_usuario.php?id=<?php echo urlencode($usuarioDetalles->getId()); ?>" class="btn btn-warning  w-100"><i class="fas fa-edit"></i> Editar cuenta</a>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="config/logout.php" class="btn btn-dark w-100"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
                    </div>
                </div>

                <!-- Sección de pedidos -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-info text-white text-center">
                            <h5 class="mb-0">Mis Pedidos</h5>
                        </div>
                        <div class="card-body">
                            <?php if (count($pedidos) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover display" id="tabla-pedidos-usuario">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Nº Pedido</th>
                                                <th>Fecha</th>
                                                <th>Total</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($pedidos as $pedido): ?>
                                                <tr>
                                                    <td>#<?php echo $pedido['id_pedido']; ?></td>
                                                    <td><?php echo date('d-m-Y', strtotime($pedido['fecha'])); ?></td>
                                                    <td><?php echo number_format($pedido['total'], 2); ?> €</td>
                                                    <td>
                                                        <span class="badge bg-<?php echo obtenerColorEstadoPedido($pedido['estado']); ?>">
                                                            <?php echo htmlspecialchars($pedido['estado']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex justify-content-start flex-wrap">
                                                            <a href="detalle_pedido.php?id_pedido=<?php echo $pedido['id_pedido']; ?>" class="btn btn-info btn-sm mb-2 mb-sm-0 me-2">Ver</a>

                                                            <?php if ($pedido['estado'] === 'Pendiente') : ?>
                                                                <form action="centro_usuario.php" method="POST" class="mb-2 mb-sm-0">
                                                                    <input type="hidden" name="id_pedido" value="<?php echo $pedido['id_pedido']; ?>" />
                                                                    <input type="hidden" name="estado" value="cancelado" />
                                                                    <button type="submit" name="cancelar_pedido" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que quieres cancelar el pedido?')">Cancelar</button>
                                                                </form>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-center text-muted">No tienes pedidos aún.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#tabla-pedidos-usuario').DataTable({
            "paging": true,
            "lengthChange": false,
            "ordering": true,
            "info": true,
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
            },
            "autoWidth": true,
            "lengthMenu": [5, 10, 25, 50, 100],
        });
    });
</script>
<?php include_once "includes/footer.php"; ?>