<?php
include_once 'config/conectar_db.php';
include_once 'gestores/gestor_productos.php';
include_once 'gestores/gestor_usuarios.php';
include_once 'gestores/gestor_familias.php';
include_once 'gestores/gestor_subfamilias.php';
// Iniciar la sesión si no está activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$pdo = conectar_db();
$gestorSubFamilias = new GestorSubFamilias($pdo);
$gestorProductos = new GestorProductos($pdo);
$gestorUsuarios = new GestorUsuarios($pdo);
// Filtrar 
$ordenar = isset($_GET['ordenar']) && in_array(strtoupper($_GET['ordenar']), ['ASC', 'DESC']) ? $_GET['ordenar'] : 'ASC';
$pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Sabores Alicante</title>
    <meta name="description" content="Web sabores Alicante">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="estilos/styles.css" rel="stylesheet"> <!-- ESTILOS PROPIOS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> <!-- BOOSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet"> <!-- ICONOS BOOSTRAP -->
    <!-- DATATABLES-->
    <link href="DataTables/datatables.min.css" rel="stylesheet">
    <script src="DataTables/datatables.min.js"></script>
</head>

<body>
    <!-- Header -->
    <header class="sticky-top bg-light text-dark shadow-sm">
        <!-- NAVBAR Solo para administradores,empleados,contables -->
        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] !== 'Usuario'): ?>
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
                <div class="container-fluid">
                    <a class="navbar-brand" href="#">Panel de Gestión</a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav">
                            <!-- Gestión de Usuarios -->
                            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] !== 'Contable'): ?>
                                <li class="nav-item">
                                    <a class="nav-link active" href="mantenimiento_usuarios.php">Gestión de Usuarios</a>
                                </li>
                                <!-- Gestión de Productos -->
                                <li class="nav-item">
                                    <a class="nav-link" href="mantenimiento_productos.php">Gestión de Productos</a>
                                </li>
                            <?php endif; ?>
                            <!-- Gestión de Familias -->
                            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'Administrador'): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="mantenimiento_familias.php">Gestión de Familias</a>
                                </li>
                                <!-- Gestión de SubFamilias -->
                                <li class="nav-item">
                                    <a class="nav-link" href="mantenimiento_subfamilias.php">Gestión de SubFamilias</a>
                                </li>
                            <?php endif; ?>
                            <!-- Gestión de Pedidos -->
                            <li class="nav-item">
                                <a class="nav-link" href="mantenimiento_pedidos.php">Gestión de Pedidos</a>
                            </li>
                            <!-- Gestión de Ventas -->
                            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] !== 'Empleado'): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="visor_ventas.php">Visor de Ventas</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                        <!-- Botón Logout -->
                        <form action="config/logout.php" method="POST" class="d-flex ms-auto">
                            <button type="submit" class="btn btn-light">Cerrar Sesión</button>
                        </form>
                    </div>
                </div>
            </nav>
        <?php endif; ?>
        <!-- NAVBAR Solo para usuarios normales -->
        <?php if (!isset($_SESSION['rol']) || $_SESSION['rol'] === 'Usuario'): ?>
            <nav class="navbar navbar-expand-lg navbar-light">
                <div class="container-fluid">
                    <!-- Logo centrado -->
                    <a href="index.php" class="logo-btn d-block">
                        <img src="estilos/logo.png" alt="Logo Sabores Alicante" class="logo-navbar">
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo02" aria-controls="navbarTogglerDemo02" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarTogglerDemo02">
                        <!-- Menú a la izquierda -->
                        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                            <li class="nav-item">
                                <a class="nav-link active" aria-current="page" href="index.php">Inicio</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">Quienes somos</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">Contacto</a>
                            </li>
                        </ul>
                        <!-- Buscador -->
                        <form class="d-flex mx-auto w-50 mb-4" role="search" method="get">
                            <input class="form-control me-2" type="search" placeholder="Buscar productos..." aria-label="Buscar" name="buscar_producto" value="">
                            <button class="btn btn-outline-primary btn-lg" type="submit">Buscar</button>
                        </form>
                        <!-- Menú a la derecha -->
                        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                            <li class="nav-item">
                                <a class="nav-link bi bi-person-fill me-4 fs-5 text-dark"
                                    href="<?php echo isset($_SESSION['usuario']) && $_SESSION['acceso'] === true ? 'centro_usuario.php' : 'acceso.php'; ?>"
                                    aria-label="Mi Usuario">
                                    <?php echo isset($_SESSION['usuario']) && $_SESSION['acceso'] === true ? 'Mi Usuario' : 'Iniciar sesión'; ?>
                                </a>
                            </li>
                            <li class="nav-item position-relative">
                                <a class="nav-link bi bi-cart me-4 fs-5 text-dark" href="carrito.php" aria-label="Mi Carrito">Mi Carrito
                                    <span class="badge bg-success rounded-circle position-absolute translate-middle p-2">
                                        <?php
                                        if (isset($_SESSION['carrito'])) {
                                            $totalArticulos = 0; //inicializa en 0
                                            foreach ($_SESSION['carrito'] as $articulo) {
                                                $totalArticulos += $articulo['cantidad']; //recorre el nº de articulos del carrito y los suma para dar el total
                                            }
                                            echo $totalArticulos; //Muestral el total
                                        } else {
                                            echo 0;
                                        }
                                        ?>
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
            <!-- Línea inferior decorativa -->
            <div class="_linea-inf"></div>
        <?php endif; ?>

    </header>