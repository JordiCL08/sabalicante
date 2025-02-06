<div class="container-fluid">
    <?php
    // Obtener valores de búsqueda y filtros
    $buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
    $ordenar = isset($_GET['ordenar']) ? $_GET['ordenar'] : 'ASC'; // Predeterminado: ASC
    $familia = isset($_GET['familia']) ? $_GET['familia'] : null;
    $subfamilia = isset($_GET['subfamilia']) ? $_GET['subfamilia'] : null;

    //Llamamos al método para obtener los productos con el campo de ordenación
    list($productos, $total_paginas) = $gestorProductos->mostrar_productos($buscar, $ordenar, $familia, $subfamilia);

    if (!empty($productos)) :
    ?>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <form method="GET" class="d-flex">
                <!-- Mantener los valores actuales -->
                <input type="hidden" name="buscar" value="<?php echo htmlspecialchars($buscar); ?>">
                <input type="hidden" name="familia" value="<?php echo htmlspecialchars($familia); ?>">
                <input type="hidden" name="subfamilia" value="<?php echo htmlspecialchars($subfamilia); ?>">
                <input type="hidden" name="campo_orden" value="precio">
                <!-- Selección para ordenar los productos por precio -->
                <select name="ordenar" class="form-select" onchange="this.form.submit()">
                    <option value="ASC" <?php echo $ordenar == 'ASC' ? 'selected' : ''; ?>>Precio: Menor a Mayor</option>
                    <option value="DESC" <?php echo $ordenar == 'DESC' ? 'selected' : ''; ?>>Precio: Mayor a Menor</option>
                </select>
            </form>
        </div>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
            <?php foreach ($productos as $producto): ?>
                <?php if ($producto->getActivo() === 1): ?>
                    <div class="col">
                        <div class="card shadow-sm border-0 rounded-3 h-100">
                            <?php if ($producto->getImagen()): ?>
                                <img src="<?php echo "imagenes/" . htmlspecialchars($producto->getImagen()); ?>"
                                    class="card-img-top rounded-top"
                                    alt="Imagen de <?php echo htmlspecialchars($producto->getNombre()); ?>"
                                    style="object-fit: cover; height: 150px;" loading="lazy">
                            <?php else: ?>
                                <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 150px;">
                                    <span class="text-muted">Sin imagen</span>
                                </div>
                            <?php endif; ?>
                            <div class="card-body bg-light text-dark d-flex flex-column">
                                <h5 class="card-title mb-2"><?php echo htmlspecialchars($producto->getNombre()); ?></h5>
                                <p class="card-text mb-2"><?php echo htmlspecialchars($producto->getDescripcion()); ?></p>

                                <div class="mt-auto">
                                    <?php
                                    // Mostrar el precio con descuento si existe
                                    $precio = $producto->getPrecio();
                                    $descuento = $producto->getDescuento(); // Porcentaje de descuento
                                    $precio_con_descuento = $descuento ? $precio * (1 - $descuento / 100) : $precio;
                                    ?>

                                    <p class="card-text mb-1">
                                        <?php if ($descuento): ?>
                                            <small class="text-muted text-decoration-line-through"><?php echo number_format($precio, 2, ',', '.') . " €"; ?></small>
                                        <?php endif; ?>
                                        <strong><?php echo number_format($precio_con_descuento, 2, ',', '.') . " €"; ?></strong>
                                    </p>
                                    <?php if (($producto->getStock()) <= 0): ?>
                                        <p class="card-text mt-2"><span class="badge bg-danger">Sin stock</span></p>
                                    <?php endif; ?>
                                    <!-- Formulario para agregar el producto al carrito -->
                                    <form action="carrito/agregar_carrito.php?<?php echo $_SERVER['QUERY_STRING']; ?>" method="POST">
                                        <input type="hidden" name="codigo" value="<?php echo $producto->getCodigo(); ?>">
                                        <input type="hidden" name="cantidad" value="1">
                                        <button type="submit" class="btn btn-sm btn-success w-100 mt-2">Añadir al carrito</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- Paginación -->
        <div class="d-flex justify-content-center flex-wrap gap-2 mt-5">
            <?php if ($total_paginas > 1): ?>
                <?php
                $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
                $parametros_paginacion = http_build_query([
                    'buscar' => $buscar,
                    'ordenar' => $ordenar,
                    'familia' => $familia,
                    'subfamilia' => $subfamilia,
                ]);
                ?>

                <!-- Botón "Anterior" -->
                <?php if ($pagina_actual > 1): ?>
                    <a href="?pagina=<?php echo $pagina_actual - 1; ?>&<?php echo $parametros_paginacion; ?>"
                        class="btn btn-outline-primary btn-lg rounded-pill me-3 shadow-sm">
                        <i class="bi bi-arrow-left"></i> Anterior
                    </a>
                <?php endif; ?>

                <!-- Páginas numeradas -->
                <?php for ($pagina = 1; $pagina <= $total_paginas; $pagina++): ?>
                    <a href="?pagina=<?php echo $pagina; ?>&<?php echo $parametros_paginacion; ?>"
                        class="btn btn-outline-primary btn-lg rounded-pill me-2 <?php echo ($pagina == $pagina_actual) ? 'active' : ''; ?>">
                        <?php echo $pagina; ?>
                    </a>
                <?php endfor; ?>

                <!-- Botón "Siguiente" -->
                <?php if ($pagina_actual < $total_paginas): ?>
                    <a href="?pagina=<?php echo $pagina_actual + 1; ?>&<?php echo $parametros_paginacion; ?>"
                        class="btn btn-outline-primary btn-lg rounded-pill ms-3 shadow-sm">
                        Siguiente <i class="bi bi-arrow-right"></i>
                    </a>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    <?php else: ?>
        <p class="text-center text-muted">No se encontraron productos.</p>
    <?php endif; ?>
</div>