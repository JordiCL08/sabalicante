<?php
session_start();
include_once 'config/conectar_db.php';
include_once 'gestores/gestor_usuarios.php';
$pdo = conectar_db();
$gestorUsuarios = new GestorUsuarios($pdo);
$ID_usuario = $_SESSION['id'];
$usuarioDetalles = $gestorUsuarios->obtener_usuario_por_id($ID_usuario);

?>
<?php include_once "includes/header.php"; ?>

<div class="container-fluid min-vh-100 bg-transparent justify-content-center align-items-center py-5">
    <div class="row w-100 justify-content-center">
        <main class="col-md-8 col-lg-6 bg-white p-5 rounded shadow-lg">
            <div class="text-center mb-4">
                <h2 class="text-primary font-weight-bold">Realizar Pago</h2>
                <p class="text-muted">Selecciona el método de pago para completar tu compra. La seguridad es nuestra prioridad.</p>
            </div>

            <form id="formulario-pago" action="vendor/checkout.php" method="POST">

                <!-- Sección para la Dirección de Entrega -->
                <div class="mb-4">
                    <h3 class="text-primary">Dirección de Entrega</h3>
                    <!-- Calle -->
                    <div class="mb-3">
                        <label for="direccion" class="form-label">Dirección</label>
                        <input type="text" class="form-control" id="direccion" name="direccion" value="<?php echo htmlspecialchars($usuarioDetalles->getDireccion()); ?>" required>
                    </div>
                    <!-- Provincia -->
                    <div class="mb-3">
                        <label for="provincia" class="form-label">Provincia</label>
                        <input type="text" class="form-control" id="provincia" name="provincia" value="<?php echo htmlspecialchars($usuarioDetalles->getProvincia()); ?>" required>
                    </div>
                    <!-- Localidad -->
                    <div class="mb-3">
                        <label for="localidad" class="form-label">Ciudad</label>
                        <input type="text" class="form-control" id="localidad" name="localidad" value="<?php echo htmlspecialchars($usuarioDetalles->getLocalidad()); ?>" required>
                    </div>
                    <!-- Código Postal -->
                    <div class="mb-3">
                        <label for="cp" class="form-label">Código Postal</label>
                        <input type="text" class="form-control" id="cp" name="cp" value="<?php echo htmlspecialchars($usuarioDetalles->getCp()); ?>" required>
                    </div>
                </div>

                <!-- Selección de forma de Pago -->

                <div class="mb-4">
                    <label for="forma-pago" class="form-label">Forma de Pago</label>
                    <select id="forma-pago" name="forma_pago" class="form-select form-control-lg" required>
                        <option value="tarjeta">Tarjeta de Crédito / Débito o Paypal</option>
                        <option value="transferencia">Transferencia Bancaria</option>
                        <option value="efectivo">Efectivo</option>
                    </select>
                    <div class="invalid-feedback">Por favor, selecciona un método de pago.</div>
                </div>

                <!-- Información para tarjeta -->
                <div id="tarjeta-info" class="mb-4" style="display: none;">
                    <strong>Al "Confirmar Pedido" se te redirigirá a la página de pago.</strong>
                </div>

                <!-- Información para transferencia -->
                <div id="transferencia-info" class="mb-4" style="display:none;">
                    <h5 class="text-danger">¡Recuerda!</h5>
                    <p>Realiza una transferencia bancaria a los siguientes datos:</p>
                    <p><strong>Banco:</strong> Banco Santander</p>
                    <p><strong>Cuenta:</strong> ES55 0049 1825 9173 8284 2951</p>
                    <strong>Envia el justificante de pago a : pagos@sabalicante.es</strong>
                </div>

                <!-- Información para efectivo -->
                <div id="efectivo-info" class="mb-4" style="display:none;">
                    <h5 class="text-warning">Pago en Efectivo</h5>
                    <p>Realiza el pago en efectivo al recoger tu pedido en nuestra tienda.</p>
                    <p>Dirección: Calle Pérez Galdos, San Vicente del Raspeig C.P 03690.</p>
                </div>

                <!-- Botones -->
                <div class="d-flex justify-content-between mt-4">
                    <a href="carrito.php" class="btn btn-outline-secondary">Volver al carrito</a>
                    <button type="submit" class="btn btn-success" id="btn-confirmar-pago">Confirmar Pedido</button>
                </div>
            </form>
        </main>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const forma_pago_seleccionada = document.getElementById('forma-pago');
        const tarjeta_info = document.getElementById('tarjeta-info');
        const transferencia_info = document.getElementById('transferencia-info');
        const efectivo_info = document.getElementById('efectivo-info');

        //Función para mostrar el mensaje según la forma de pago
        function seleccion_formulario(forma_de_pago) {
            tarjeta_info.style.display = 'none';
            transferencia_info.style.display = 'none';
            efectivo_info.style.display = 'none';

            switch (forma_de_pago) {
                case 'tarjeta':
                    tarjeta_info.style.display = 'block';
                    break;
                case 'transferencia':
                    transferencia_info.style.display = 'block';
                    break;
                case 'efectivo':
                    efectivo_info.style.display = 'block';
                    break;
            }
        }

        //Mostramos la primera opción
        seleccion_formulario(forma_pago_seleccionada.value);

        //Evento para mostrar la forma de pago seleccionada
        forma_pago_seleccionada.addEventListener('change', function() {
            seleccion_formulario(forma_pago_seleccionada.value);
        });
    });
</script>
<?php include_once "includes/footer.php"; ?>