<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['acceso'] !== true) {
    // Si no está logueado o la sesión no está activa, redirigimos
    header('Location: index.php');
    exit;
}

include_once 'config/conectar_db.php';
$pdo = conectar_db();

// Obtener datos de usuario desde la sesión
$usuario = $_SESSION['usuario'];
$rol = $_SESSION['rol'];
$id_usuario = $_SESSION['id'];

// Incluir los gestores necesarios
include_once 'gestores/gestor_pedidos.php';
include_once 'gestores/gestor_usuarios.php';
include_once 'gestores/gestor_productos.php';

// Crear instancias de los gestores
$gestorPedidos = new GestorPedidos($pdo);
$gestorProducto = new GestorProductos($pdo);
$gestorUsuarios = new GestorUsuarios($pdo);
//Obtenemos los ddatos del usuario logueado
$detalle_usuario = $gestorUsuarios->obtener_usuario_por_id($id_usuario);
//Nos aeguramos de tener los datos
if (!$detalle_usuario) {
    echo "No se encontraron datos para el usuario.";
    exit;
}

//Verificar si se pasó un ID de pedido 
$id_pedido = isset($_GET['id_pedido']) ? $_GET['id_pedido'] : null;

if ($id_pedido) {
    //Detalles de un solo pedido
    $pedido = $gestorPedidos->obtener_pedido_id_pedido($id_pedido, $id_usuario);
    if (!$pedido) {
        echo "No se encontró el pedido solicitado o no pertenece a este usuario.";
        exit;
    }
    //Líneas de detalle de este pedido
    $detalle_pedido = $gestorPedidos->obtener_lineas_pedido($pedido['id_pedido']);
} else {
    echo "ID de pedido no proporcionado.";
    exit;
}
?>

<?php include_once "includes/header.php"; ?>

<!-- Contenedor principal de la página -->
<div class="container-fluid d-flex flex-column min-vh-100 justify-content-center align-items-center bg-light">
    <div class="row flex-grow-1 w-100">
        <!-- Contenido principal -->
        <main class="bg-light w-100">
            <?php include_once "config/procesa_errores.php"; ?>
            <div class="container mt-5">
                <div class="row">
                    <!-- Detalle del pedido -->
                    <div class="col-12">
                        <div class="card shadow-lg">
                            <div class="card-header bg-info text-white text-center">
                                <h4>Detalles del Pedido Nº <?php echo $pedido['id_pedido']; ?></h4>
                            </div>
                            <div class="card-body">
                                <?php if (count($detalle_pedido) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Código Producto</th>
                                                    <th>Nombre</th>
                                                    <th>Descripción</th>
                                                    <th>Imagen</th>
                                                    <th>Cantidad</th>
                                                    <th>Precio</th>
                                                    <th>Precio descuento</th>
                                                    <th>Descuento</th>
                                                    <th>Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($detalle_pedido as $linea): ?>
                                                    <?php
                                                    //Obtener los detalles del producto
                                                    $producto = $gestorProducto->obtener_producto_codigo($linea['codigo']);
                                                    ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($linea['codigo']); ?></td>
                                                        <td><?php echo htmlspecialchars($producto->getNombre()); ?></td>
                                                        <td><?php echo htmlspecialchars($producto->getDescripcion()); ?></td>
                                                        <td><img src="imagenes/<?php echo $producto->getImagen(); ?>" alt="Imagen del Producto" class="img-fluid" width="50" /></td>
                                                        <td><?php echo $linea['cantidad']; ?></td>
                                                        <td><?php echo number_format($producto->getPrecio(), 2); ?> €</td>
                                                        <td><?php echo number_format($linea['precio_unitario'], 2); ?> €</td>
                                                        <td><?php
                                                            if (is_null($producto->getDescuento())) {
                                                                echo 'Sin descuento';
                                                            } else {
                                                                echo htmlspecialchars($producto->getDescuento() . ' %');
                                                            }
                                                            ?></td>
                                                        <td><?php echo number_format($linea['subtotal'], 2); ?> €</td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <!-- Mostrar los gastos de envío -->
                                                <?php if (isset($linea['recogida_local']) && $linea['recogida_local'] == false): ?>
                                                    <tr>
                                                        <td colspan="8" class="text-right"><strong>Gastos de Envío: 5 €</strong></td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-center mt-4">
                                        <h4>Total: <?php echo number_format($pedido['total'], 2); ?> €</h4>
                                    </div>
                                <?php else: ?>
                                    <p>No hay detalles para este pedido.</p>
                                <?php endif; ?>
                                <div class="text-center mt-4">
                                    <a href="javascript:history.back()" class="btn btn-secondary">Volver</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include_once "includes/footer.php"; ?>