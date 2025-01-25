<?php
include_once "includes/header.php";
?>

<!-- Contenedor principal de la página -->
<div class="container-fluid d-flex flex-column min-vh-100">
    <div class="row flex-grow-1">
        <!-- Barra lateral -->
        <aside class="col-md-3 col-lg-2 bg-secondary text-white p-4">
            <?php include_once "includes/menu_lateral.php"; ?>
        </aside>

        <!-- Contenido principal -->
        <main class="col-md-9 col-lg-10 p-4 bg-white">
            <?php include_once "config/procesa_errores.php"; ?>
            <?php include_once 'carrito/ver_carrito.php'; ?>
        </main>
    </div>
</div>
<!-- Footer -->
<?php include_once "includes/footer.php" ?>