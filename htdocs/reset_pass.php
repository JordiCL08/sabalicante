<?php
include_once 'config/conectar_db.php';

session_start();
$dni = $_SESSION['dni'] ?? null;
$errores = [];
$mensaje = [];
if ($dni === null) {
    header("Location:index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nueva_clave = trim($_POST['nueva_clave']);
    $confirmar_nueva_clave = trim($_POST['confirmar_nueva_clave']);
    

    // Validar que las contraseñas coincidan
    if ($nueva_clave !== $confirmar_nueva_clave) {
        $errores[] = "Las contraseñas no coinciden.";
    } else {
        // Si las contraseñas coinciden, ciframos la nueva contraseña
        $nueva_clave_hashed = password_hash($nueva_clave, PASSWORD_DEFAULT);
    }

    if (empty($errores) && $dni) {
        $pdo = conectar_db();
        try {
            // Ejecutar la consulta solo si no hay errores
            $query = "UPDATE usuarios SET clave = :nueva_clave WHERE dni = :dni";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':nueva_clave', $nueva_clave_hashed);
            $stmt->bindParam(':dni', $dni);
            $resultado = $stmt->execute();

            if ($resultado) {
                $_SESSION['mensaje'][] = "Contraseña cambiada con éxito.";
                session_unset();
                session_destroy();
                header("Location: index.php");
                exit();
            } else {
                $_SESSION['errores'][] = "Error al cambiar la contraseña.";
            }
        } catch (PDOException $e) {
            $_SESSION['errores'][] = "Error en la actualización de la contraseña: " . $e->getMessage();
        }
    } else {
        // Si hay errores en las contraseñas o no hay una sesión válida
        $_SESSION['errores'] = $errores;
    }
}

?>
<!-- Muestra errores -->
<?php require_once 'config/procesa_errores.php'; ?>
<?php include_once "includes/header.php"; ?>
<!-- Contenedor principal de la página -->
<div class="container-fluid d-flex flex-column min-vh-100">
    <div class="row flex-grow-1">
        <!-- Barra lateral -->
        <aside class="col-md-3 col-lg-2 bg-secondary text-white p-4">
            <?php include_once "includes/menu_lateral.php"; ?>
        </aside>

        <!-- Contenido principal -->
        <main class="col-md-9 col-lg-10 p-4 bg-white">
            <div class="container d-flex justify-content-center align-items-center">
                <!-- AQUI -->
                <div class="container p-5">
                    <!-- Muestra errores -->
                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header text-center">
                                    <h4>Restablecer Contraseña</h4>
                                </div>
                                <div class="card-body">
                                    <form method="post" action="">
                                        <!-- CONTRASEÑA NUEVA -->
                                        <div class="mb-3">
                                            <label for="nueva_clave" class="form-label">Nueva Contraseña</label>
                                            <input type="password" id="nueva_clave" name="nueva_clave" class="form-control" placeholder="Introduce la contraseña" required>
                                        </div>
                                        <!-- CONFIRMAR CONTRASEÑA NUEVA -->
                                        <div class="mb-3">
                                            <label for="confirmar_nueva_clave" class="form-label">Confirmar contraseña:</label>
                                            <input type="password" id="confirmar_nueva_clave" name="confirmar_nueva_clave" class="form-control" placeholder="Repite la contraseña" required>
                                        </div>
                                        <!-- BOTONES -->
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary">Guardar Contraseña</button>
                                        </div>
                                    </form>
                                    <div class="text-center mt-3">
                                        <a href="javascript:history.back()" class="btn btn-secondary">Volver</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- AQUI -->
        </main>
    </div>
</div>
<?php include_once "includes/footer.php"; ?>