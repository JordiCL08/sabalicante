<?php include_once "includes/header.php";?>
<!-- Contenedor principal de la página -->
<div class="container-fluid d-flex flex-column min-vh-100">
    <!-- Si hay acceso y el rol no es usuario no muestra ni el menú lateral ni los productos-->
    <?php if (isset($_SESSION['acceso']) && $_SESSION['rol'] !== 'Usuario'): ?>
        <!-- Mostrar imagen para los no usuarios-->
        <div class="row flex-grow-1 bg-light">
            <div class="col-12 d-flex justify-content-center align-items-center">
                <img src="estilos/logo.png" alt="logo sabores alicante" class="img-fluid">
            </div>
        </div>
        <!-- En el caso contrario -->
    <?php else: ?>
        <!-- Barra lateral y contenido para el usuario -->
        <div class="row flex-grow-1">
            <aside class="col-md-3 col-lg-2 bg-secondary text-white p-4">
                <?php include_once "includes/menu_lateral.php"; ?>
            </aside>
            <main class="col-md-9 col-lg-10 p-4 bg-white">
                <?php require_once('config/procesa_errores.php'); ?>
                <div class="container d-flex justify-content-center align-items-center">
                    <?php include_once 'productos.php'; ?>
                </div>
            </main>
        </div>
    <?php endif; ?>
</div>
<!-- Footer -->
<?php include_once "includes/footer.php" ?>