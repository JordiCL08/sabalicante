<?php
session_start();
include_once 'config/conectar_db.php';
include_once 'config/funciones.php';

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni = trim($_POST['dni']);
    $email = trim($_POST['email']);

    // Validaciones
    if (!comprobar_DNI($dni, $errores)) {
        $errores[] = "El DNI proporcionado no es válido.";
    }

    if (!comprobar_email($email, $errores)) {
        $errores[] = "El correo electrónico proporcionado no es válido.";
    }
    if (empty($errores)) {
        $pdo = conectar_db();

        try {
            $query = "SELECT * FROM usuarios WHERE dni = :dni AND email = :email";
            $stmt = $pdo->prepare($query);
            $stmt->bindValue(':dni', $dni);
            $stmt->bindValue(':email', $email);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                $_SESSION['dni'] = $dni;
                $_SESSION['email'] = $email;
                header('Location: reset_pass.php');
                exit();
            } else {
                $_SESSION['errores'][] = "Revisa que el DNI  y el Email sean correctos.";
            }
        } catch (PDOException $e) {
            $_SESSION['errores'][] = "Error en la consulta: " . $e->getMessage();
        }
    } else {
        $_SESSION['errores'] = $errores;
    }

    header('Location: recuperar_pass.php');
    exit();
}
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
        <main class="col-md-9 col-lg-10 p-4 bg-white">
            <!-- Muestra errores -->
            <?php require_once 'config/procesa_errores.php'; ?>
            <div class="container d-flex justify-content-center align-items-center">
                <!-- AQUI -->
                <div class="container p-8">
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header text-center">
                                    <h4>Recuperar Contraseña</h4>
                                </div>
                                <div class="card-body">
                                    <form method="post" action="">
                                        <!-- DNI -->
                                        <div class="mb-3">
                                            <label for="dni" class="form-label">DNI</label>
                                            <input type="text" id="dni" name="dni" class="form-control" required>
                                        </div>
                                        <!-- Email -->
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Correo Electrónico</label>
                                            <input type="email" id="email" name="email" class="form-control" required>
                                        </div>
                                        <!-- BOTONES -->
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary">Enviar</button>
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