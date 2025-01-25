<?php
session_start();

include_once "includes/header.php";
?>

<!-- Contenedor principal de la página -->
<div class="container-fluid d-flex flex-column min-vh-100 bg-light">
    <div class="row flex-grow-1">
        <!-- Barra lateral -->
        <aside class="col-md-3 col-lg-2 bg-secondary text-white p-4">
            <?php include_once "includes/menu_lateral.php"; ?>
        </aside>

        <!-- Contenido principal -->
        <main class="col-md-9 col-lg-10 p-4 bg-white rounded-3 shadow-lg">
            <!-- Muestra errores, si los hay -->
            <?php require_once('config/procesa_errores.php'); ?>
            <?php if (empty($_SESSION['acceso'])): ?>
                <!-- Mostrar login y registro si no se ha iniciado sesión -->
                <section id="login-registro" class="container-fluid my-4">
                    <div class="row justify-content-center">
                        <div class="col-12 col-md-10 col-lg-8">
                            <div class="d-flex flex-column flex-sm-row align-items-start gap-3 p-4 border rounded-3 shadow-sm">
                                <!-- Registro de nuevos clientes -->
                                <div class="card p-3 me-4 flex-fill shadow-sm mb-3 mb-sm-0">
                                    <h5 class="text-center">Nuevo Cliente</h5>
                                    <p>¿Necesitas una cuenta?</p>
                                    <p class="text-muted">
                                        Al crear una cuenta en <span class="fw-bold">Sabores Alicante</span>, podrás realizar tus compras rápidamente,
                                        revisar el estado de tus pedidos y consultar tus anteriores operaciones.
                                    </p>
                                    <div class="text-center mt-3">
                                        <button type="button" onclick="window.location.href='nuevo_usuario.php'" class="btn btn-outline-primary w-100">Regístrate</button>
                                    </div>
                                </div>

                                <!-- Login de clientes existentes -->
                                <div class="card p-3 flex-fill shadow-sm">
                                    <h5 class="text-center">Ya soy cliente</h5>
                                    <form action="acceso.php" method="POST">
                                        <div class="mb-3">
                                            <label for="usuario" class="form-label">E-mail:</label>
                                            <input type="email" name="usuario" id="usuario" class="form-control" required>
                                            <div class="invalid-feedback">Por favor, introduce un email válido.</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="clave" class="form-label">Contraseña:</label>
                                            <input type="password" name="clave" id="clave" class="form-control" required>
                                            <div class="invalid-feedback">Por favor, introduce tu contraseña.</div>
                                        </div>
                                        <div class="d-flex flex-column flex-sm-row justify-content-between">
                                            <button type="submit" name="entrar" class="btn btn-primary w-100 mb-2 mb-sm-0">Entrar</button>
                                        </div>
                                        <div class="mt-2 text-center">
                                            <button type="button" onclick="window.location.href='recuperar_pass.php'" class="btn btn-warning w-100">¿Has olvidado tu contraseña?</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            <?php else: ?>
                <!-- Mostrar contenido del pedido si el usuario está logueado -->
                <section id="detalle-pedido" class="container-fluid my-4">
                    <h2 class="text-center mb-4">Resumen del Pedido</h2>
                    <?php
                    // Verificar si hay productos en el carrito
                    if (isset($_SESSION['carrito']) && !empty($_SESSION['carrito'])):
                        $total = 0;
                        $stock_insuficiente = false;
                    ?>
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
                                        <th>Descuento</th>
                                        <th>Precio descuento</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($_SESSION['carrito'] as $producto): ?>
                                        <?php
                                        // Calcular el subtotal del producto
                                        $subtotal = $producto['cantidad'] * $producto['precio_final'];
                                        $total += $subtotal;
                                        $_SESSION['total'] = $total;

                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($producto['codigo']); ?></td>
                                            <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                            <td><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                                            <td><img src="imagenes/<?php echo htmlspecialchars($producto['imagen']); ?>" alt="Imagen del Producto" class="img-fluid" style="max-width: 80px;"></td>
                                            <td><?php echo $producto['cantidad']; ?></td>
                                            <td><?php echo number_format($producto['precio'], 2); ?> €</td>
                                            <td><?php echo is_null($producto['descuento']) ? 'Sin descuento' : htmlspecialchars($producto['descuento'] . ' %'); ?></td>
                                            <td><?php echo number_format($producto['precio_final'], 2); ?> €</td>
                                            <td><?php echo number_format($subtotal, 2); ?> €</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-4">
                            <h4>Total: <?php echo number_format($total, 2); ?> €</h4>
                        </div>
                    <?php else: ?>
                        <p class="text-center">No hay productos en tu carrito.</p>
                    <?php endif; ?>

                    <div class="d-flex justify-content-center justify-content-md-end mt-4">
                        <a class="btn btn-success me-2" href="pago.php">Confirmar Pedido</a>
                        <a href="javascript:history.back()" class="btn btn-outline-secondary">Volver</a>
                    </div>
                </section>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Footer -->
<?php include_once "includes/footer.php"; ?>