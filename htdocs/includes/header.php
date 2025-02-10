<?php
// Iniciar la sesión si no está activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once(__DIR__ . '/../config/conectar_db.php');
include_once(__DIR__ . '/../gestores/gestor_productos.php');
include_once(__DIR__ . '/../gestores/gestor_usuarios.php');
include_once(__DIR__ . '/../gestores/gestor_familias.php');
include_once(__DIR__ . '/../gestores/gestor_subfamilias.php');
include_once(__DIR__ . '/../gestores/gestor_carritos.php');

$pdo = conectar_db();
$gestorSubFamilias = new GestorSubFamilias($pdo);
$gestorProductos = new GestorProductos($pdo);
$gestorUsuarios = new GestorUsuarios($pdo);
//Ordenar y paginación
/*En el caso de que "ordenar" no este definido ordena por ascendente, comparamos los valores con descendente
    si es verdadero asigna desc y si no asc*/
$ordenar = strtoupper($_GET['ordenar'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
//Si no hay nº de página asigna 1 por defecto.
$pagina = (int) ($_GET['pagina'] ?? 1);
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
    <link rel="icon" type="image/png" sizes="32x32" href="/estilos/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/estilos/favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/estilos/favicon/favicon-16x16.png">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/estilos/favicon/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <!-- DATATABLES-->
    <link href="DataTables/datatables.min.css" rel="stylesheet">
    <script src="DataTables/datatables.min.js"></script>
</head>

<body>
    <!-- Header -->
    <header class="sticky-top bg-light text-dark shadow-sm">
        <!-- NAVBAR Solo para administradores,empleados,contables -->
        <?php if (isset($_SESSION['acceso']) && $_SESSION['rol'] !== 'Usuario'): ?>
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
                <div class="container-fluid">
                    <a class="navbar-brand" href="index.php">Panel de Gestión</a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#hamburguesa_menu" aria-controls="hamburguesa_menu" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="hamburguesa_menu">
                        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                            <!-- Gestión de Usuarios -->

                            <?php if (isset($_SESSION['acceso']) && $_SESSION['rol'] !== 'Contable'): ?> <!--Aquí no tienen acceso los contables-->
                                <li class="nav-item">
                                    <a class="nav-link active" href="mantenimiento_usuarios.php">Gestión de Usuarios</a>
                                </li>
                                <!-- Gestión de Productos -->
                                <li class="nav-item">
                                    <a class="nav-link" href="mantenimiento_productos.php">Gestión de Productos</a>
                                </li>
                                <!-- Gestión de Familias -->
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
                            <?php if (isset($_SESSION['acceso']) && $_SESSION['rol'] !== 'Empleado'): ?><!--Aquí no tienen acceso los Empleados -->
                                <li class="nav-item">
                                    <a class="nav-link" href="visor_ventas.php">Visor de Ventas</a>
                                </li>
                            <?php endif; ?>

                            <!-- LOGS -->
                            <?php if (isset($_SESSION['acceso']) && $_SESSION['rol'] === 'Administrador'): ?><!--Aquí solo tiene acceso los Administradores-->
                                <div class="vr mx-3"></div> <!-- Separador -->
                                <li class="nav-item">
                                    <a class="nav-link" href="visor_logs.php">LOGS</a>
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
        <!-- NAVBAR Solo para usuarios normales, registrados o no -->
        <?php if (!isset($_SESSION['acceso']) || $_SESSION['rol'] === 'Usuario'): ?>
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <!-- Logo centrado -->
                    <a href="index.php" class="logo-btn d-block mx-auto">
                        <img src="estilos/logo.png" alt="Logo Sabores Alicante" class="logo-navbar">
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#hamburguesa_menu" aria-controls="hamburguesa_menu" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="hamburguesa_menu">
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
                            <li class="nav-item">
                                <a class="nav-link" href="#">Envío</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">Devoluciones</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">Promociones</a>
                            </li>
                        </ul>

                        <!-- Buscador-->
                        <form class="d-flex mx-auto mb-2" role="search" method="get" action="index.php" style="max-width: 800px; width: 100%;">
                            <input class="form-control me-2" type="search" placeholder="Buscar productos..." aria-label="Buscar" name="buscar" value="">
                            <button class="btn btn-outline-primary" type="submit">Buscar</button>
                        </form>

                        <!-- Menú a la derecha -->
                        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                            <li class="nav-item">
                                <!-- En el caso de que haya acceso por parte del usuario, ve el centro usuario(con sus datos) -->
                                <a class="nav-link bi bi-person-fill me-4 fs-5 text-dark"
                                    href="<?php echo isset($_SESSION['usuario']) && $_SESSION['acceso'] === true ? 'centro_usuario.php' : 'acceso.php'; ?>"
                                    aria-label="Mi Usuario">
                                    <?php echo isset($_SESSION['usuario']) && $_SESSION['acceso'] === true ? 'Mi Usuario' : 'Iniciar sesión'; ?>
                                </a>
                            </li>
                            <li class="nav-item position-relative">
                                <a class="nav-link bi bi-cart me-4 fs-5 text-dark" href="carrito.php" aria-label="Mi Carrito">
                                    Mi Carrito
                                    <span class="badge bg-success rounded-circle position-absolute translate-middle p-2">
                                        <?php
                                        // Calcular el total de productos en el carrito
                                        if (isset($_SESSION['carrito'])) {
                                            //si el carrito no es null, cuenta la cantidad de productos en el carrito
                                            $total_productos = array_reduce($_SESSION['carrito'], function ($total, $producto) {
                                                return $total + $producto['cantidad']; //Suma el nº de unidades de cada producto
                                            }, 0);
                                            echo $total_productos;
                                        } else {
                                            echo 0; //si es null (esta vacio) 
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