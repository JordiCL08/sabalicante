<?php
include('config/conectar_db.php');
session_start();
?>
<?php include_once "includes/header.php"; ?>
<!-- Contenedor principal de la página -->
<div class="container-fluid d-flex flex-column min-vh-100">
    <div class="row flex-grow-1">
        <!-- Barra lateral -->
        <aside class="col-md-3 col-lg-2 bg-secondary text-white p-4">
            <?php include_once "includes/menu_lateral.php"; ?>
        </aside>
        <!-- Contenido principal -->
        <main class="col-md-9 col-lg-10 bg-light p-4">
            <?php include_once "config/procesa_errores.php"; ?>
            <div class="container mt-5">
                <div class="row">
                    <!-- Detalle del pedido -->
                    <div class="col-md-12">
                        <div class="card shadow-lg">
                            <div class="card-header bg-info text-white">
                                <h3 class="text-center mb-0">¡Gracias por tu compra!</h3>
                            </div>
                            <div class="card-body">
                                <p class="lead text-center">
                                    Esperamos que hayas tenido una buena experiencia.
                                </p>
                                <hr>

                                <div class="row">
                                    <!-- Información del pedido -->
                                    <div class="col-md-6">
                                        <h5 class="mb-3">Detalles del Pedido:</h5>
                                        <ul class="list-unstyled">
                                            <li><strong>Número de pedido:</strong> <?php echo $_SESSION['id_pedido']; ?></li>
                                            <li><strong>Fecha del pedido:</strong> <?php echo $_SESSION['fecha_pedido']; ?></li>
                                            <li><strong>Envío:</strong> Estándar (2-3 días laborables)</li>
                                        </ul>
                                    </div>

                                    <!-- Información de seguimiento -->
                                    <div class="col-md-6">
                                        <h5 class="mb-3">Seguimiento del Pedido:</h5>
                                        <p>En cuanto te lo enviemos, recibirás un mensaje de correo electrónico con el número de seguimiento.</p>
                                        <p>Para comprobar el estado de tu pedido en cualquier momento, dirígete a <a href="centro_usuario.php" class="text-info">Tu panel de usuario</a>.</p>
                                    </div>
                                </div>

                                <hr>

                                <!-- Política de cancelación y devoluciones -->
                                <div class="alert alert-info">
                                    <h5 class="alert-heading">¿Necesitas cancelar tu pedido?</h5>
                                    <p>
                                        Podrás cancelar tu pedido mientras se encuentre en el estado <strong>Pendiente</strong>.
                                        Una vez que el pedido se esté tramitando, ya no podrá ser cancelado.
                                    </p>
                                    <p>
                                        Para más información, consulta nuestra <a href="#" class="text-info">Política de Devoluciones</a>.
                                    </p>
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