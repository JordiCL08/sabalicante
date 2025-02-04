<?php
session_start();
if (isset($_SESSION['acceso']) && $_SESSION['acceso'] === true) {
    header("Location: index.php");
    exit;
}
require_once "gestores/gestor_usuarios.php";
require_once "gestores/gestor_carritos.php";
require_once "config/conectar_db.php";

/*+++++++++++++++++++++++++++++++++++++++++
   PROCESAR EL FORMULARIO DE LOGIN
 +++++++++++++++++++++++++++++++++++++++++*/

$errores = [];

if (isset($_POST["entrar"])) {
    // Obtener los datos del formulario
    $usuario = trim($_POST['usuario']); //EMAIL
    $clave = trim($_POST['clave']);


    // Validar los campos de entrada
    if (empty($usuario) || empty($clave)) {
        $errores[][] = "El usuario y la contraseña son obligatorios.";
    } else {
        try {
            // Conectar a la base de datos
            $pdo = conectar_db();
            $query = "SELECT id, nombre, clave, email, rol,activo FROM usuarios WHERE email = :email";
            $stmt = $pdo->prepare($query);
            $stmt->bindValue(":email", $usuario);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($resultado) {
                // Verificar si la cuenta está activa
                if (isset($resultado['activo']) && $resultado['activo'] === 0) {
                    escribir_log("Hubo un intento acceso de un usuario desactivado-> $usuario.", 'acceso');
                    $alerta_usuario_desactivado = "El usuario está desactivado, ponte en contacto con admin@sabalicante.com.";
                } else {
                    // Verificar la contraseña
                    if (password_verify($clave, $resultado['clave'])) {
                        // Crear las variables de sesión
                        $_SESSION['acceso'] = true;
                        $_SESSION['usuario'] = $resultado['email'];
                        $_SESSION['id'] = $resultado['id'];
                        $_SESSION['rol'] = $resultado['rol'];
                        $_SESSION['nombre'] = $resultado['nombre'];
                        //mensaje bienvenida
                        $_SESSION['mensaje'] = "Bienvendi@ " . $_SESSION['nombre'];
                        //Carga el carrito del usuario
                        cargar_carrito($_SESSION['id']);
                        // Redirigir al usuario a la página principal
                        header("Location: index.php");
                        exit;
                    } else {
                        $errores[] = "La contraseña es incorrecta.";
                    }
                }
            } else {
                escribir_log("Hubo un intento acceso de un usuario no registrado.", 'acceso');
                $errores[] = "El usuario no está registrado.";
            }
        } catch (PDOException $e) {
            $errores[] = "Error en la consulta: " . $e->getMessage();
        }
    }
    //Almacenar los errores en la sesión
    $_SESSION['errores'] = $errores;
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
        <main class="col-md-9 col-lg-10 p-4 bg-light">

            <?php
            include_once "config/procesa_errores.php"; //Errores sesiones
            //Error usuario fuera de la sesion por estar desactivado
            if (isset($alerta_usuario_desactivado)) {
                echo '<ul class="alert alert-danger">';
                echo '<li>' . htmlspecialchars($alerta_usuario_desactivado) . '</li>';
                echo '</ul>';
            }
            ?>
            <div class="container d-flex justify-content-center align-items-center">
                <div class="col-12 col-md-8 col-lg-6">
                    <div class="card shadow-lg p-4 border-0 rounded-3">
                        <div class="card-body">
                            <h1 class="text-center text-primary mb-4">Acceso Clientes</h1>
                            <p class="text-center mb-5 text-muted">Introduce tu Usuario y contraseña para acceder.</p>
                            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                                <!-- USUARIO/EMAIL-->
                                <div class="form-group mb-4">
                                    <label for="usuario" class="form-label">Email:</label>
                                    <input type="email" id="usuario" name="usuario" class="form-control form-control-lg" placeholder="Introduce tu email" required>
                                </div>
                                <!-- CONTRASEÑA -->
                                <div class="form-group mb-4">
                                    <label for="clave" class="form-label">Contraseña:</label>
                                    <input type="password" id="clave" name="clave" class="form-control form-control-lg" placeholder="Introduce tu contraseña" required>
                                </div>
                                <!-- BOTÓN ENTRAR -->
                                <button type="submit" name="entrar" class="btn btn-primary btn-block w-100 py-2">Entrar</button>

                                <!-- Enlaces adicionales -->
                                <div class="text-center mt-3">
                                    <button type="button" onclick="window.location.href='nuevo_usuario.php'" class="btn btn-secondary w-100 py-2">Registrar Cliente</button>
                                </div>
                                <div class="text-center mt-3">
                                    <button type="button" onclick="window.location.href='recuperar_pass.php'" class="btn btn-warning w-100 py-2">Recuperar Contraseña</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>


<?php include_once "includes/footer.php"; ?>