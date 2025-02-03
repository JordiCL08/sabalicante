<?php
include_once "actualizar_cantidad.php";

if (isset($_SESSION['carrito']) && count($_SESSION['carrito']) > 0) {
    $total = 0;
?>
    <h2 class="mb-4 text-center">Carrito de Compras</h2>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Original</th>
                    <th>Precio con Descuento</th>
                    <th>Descuento</th>
                    <th>Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($_SESSION['carrito'] as $index => $producto) {
                    // Validar y aplicar descuento, asegurándonos de que el valor sea un número
                    $descuento = isset($producto['descuento']) && is_numeric($producto['descuento']) ? $producto['descuento'] / 100 : 0;
                    $precio_final = $producto['precio'] * (1 - $descuento);  // Aplica el descuento al precio                    
                    $subtotal = $precio_final * $producto['cantidad'];
                    $total += $subtotal;
                ?>
                    <tr id="producto_<?= htmlspecialchars($producto['codigo']) ?>">
                        <td><?= htmlspecialchars($producto['nombre']) ?></td>
                        <td>
                            <input type="number"
                                name="cantidad"
                                value="<?= htmlspecialchars($producto['cantidad']) ?>"
                                min="1"
                                class="form-control form-control-sm cantidad"
                                data-codigo="<?= htmlspecialchars($producto['codigo']) ?>"
                                aria-label="Cantidad de <?= htmlspecialchars($producto['nombre']) ?>"
                                style="width: 80px;">
                        </td>

                        <!-- Precio original (sin descuento) -->
                        <td id="precio_original_<?= htmlspecialchars($producto['codigo']) ?>"><?= number_format($producto['precio'], 2) ?> €</td>

                        <!-- Precio con descuento -->
                        <td id="precio_final_<?= htmlspecialchars($producto['codigo']) ?>"><?= number_format($producto['precio_final'], 2) ?> €</td>

                        <!-- Mostrar el porcentaje de descuento, si existe -->
                        <td id="descuento_<?= htmlspecialchars($producto['codigo']) ?>">
                            <?php if ($descuento > 0) { ?>
                                <?= number_format($descuento * 100, 0) ?>%
                            <?php } else { ?>
                                No aplica
                            <?php } ?>
                        </td>

                        <!-- Subtotal -->
                        <td id="subtotal_<?= htmlspecialchars($producto['codigo']) ?>"><?= number_format($subtotal, 2) ?> €</td>

                        <!-- Botón de eliminar -->
                        <td>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este producto del carrito?');">
                                <input type="hidden" name="eliminar" value="<?= htmlspecialchars($producto['codigo']) ?>"> 
                                <button type="submit" class="btn btn-danger btn-sm" title="Eliminar del carrito"><i class="bi bi-trash"></i> Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Mostrar el total -->
    <div class="d-flex justify-content-between align-items-center mt-4">
        <h4 class="text-end">
            <strong>Total: </strong>
            <span class="text-success" id="total"><?= number_format($_SESSION['total'] = $total, 2) ?> €</span>
        </h4>
    </div>

    <!-- Botones para tramitar pedido o seguir comprando -->
    <div class="d-flex justify-content-between mt-4">
        <a href="procesar_pedido.php" class="btn btn-success btn-lg shadow-lg col-12 col-md-5">Tramitar Pedido</a>
        <a href="index.php" class="btn btn-primary btn-lg shadow-lg col-12 col-md-5">Seguir comprando</a>
    </div>

<?php } else { ?>
    <div class="alert alert-warning text-center mt-4">
        <strong>¡Tu carrito está vacío!</strong> Añade productos para comenzar a comprar.
    </div>
<?php } ?>


<script>
    document.querySelectorAll('.cantidad').forEach(function(input) {
        input.addEventListener('change', function() {
            var codigo = this.getAttribute('data-codigo');
            var cantidad = this.value;

            //Enviar la nueva cantidad al servidor 
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'carrito.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status == 200) {
                    location.reload(); //Recargar la página para reflejar los cambios
                }
            };
            xhr.send('codigo=' + codigo + '&cantidad=' + cantidad);
        });
    });
</script>