<?php
session_start();
include_once "config/conectar_db.php";
include_once "gestores/gestor_usuarios.php";

$pdo = conectar_db();
$gestorUsuarios = new GestorUsuarios($pdo);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recolección de datos del formulario
    $dni = trim($_POST['dni']);
    $nombre = trim($_POST['nombre']);
    $telefono = trim($_POST['telefono']);
    $apellidos = trim($_POST['apellidos']);
    $email = trim($_POST['email']);
    $clave = trim($_POST['clave']);
    $confirmar_clave = trim($_POST['confirmar_clave']);

    $errores = [];

    // Validar campos requeridos
    if (
        empty($dni) || empty($nombre) || empty($telefono) || empty($email) || empty($clave)  || empty($apellidos)
    ) {
        $errores[] = "Todos los campos son obligatorios.";
    }

    // Validar formato del DNI
    if (!comprobar_DNI($dni, $errores)) {
        $errores[] = "El DNI proporcionado no es válido.";
    }

    // Validar formato del email
    if (!comprobar_email($email, $errores)) {
        $errores[] = "El correo electrónico no es válido.";
    }

    // Verificar si el DNI ya existe en la base de datos
    try {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE dni = :dni");
        $stmt->bindValue(':dni', $dni);
        $stmt->execute();

        if ($stmt->fetch()) {
            $errores[] = "El DNI $dni ya está registrado.";
        }
    } catch (PDOException $e) {
        $errores[] = "Error al verificar el DNI: " . $e->getMessage();
    }

    // Verificar si el EMAIL/USUARIO ya existe en la base de datos
    try {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->bindValue(':email', $email);
        $stmt->execute();

        if ($stmt->fetch()) {
            $errores[] = "El correo electronico: $email ya está registrado.";
        }
    } catch (PDOException $e) {
        $errores[] = "Error al verificar el Correo Electronico: " . $e->getMessage();
    }

    // Validar que las contraseñas coincidan
    if ($clave !== $confirmar_clave) {
        $errores[] = "Las contraseñas no coinciden.";
    } else {
        // Cifrar la contraseña solo si no hay errores en validación previa
        $clave = password_hash($clave, PASSWORD_DEFAULT);
    }

    // Validar teléfono
    if (!preg_match('/^\d{9}$/', $telefono)) {
        $errores[] = "El número de teléfono debe tener 9 dígitos.";
    }

    // Si no hay errores, registrar al cliente
    if (empty($errores)) {
        try {
            $usuario = new Usuario(null, $clave, $dni, $nombre, $apellidos, '', '', '', '', $telefono, $email, 'usuario', 1);
            $resultado = $gestorUsuarios->crear_usuario($usuario);

            if ($resultado) {
                $nom_usuario  = $usuario->getNombre();
                $creador_usuario = !empty($_SESSION['usuario']) ? $_SESSION['usuario'] : $nom_usuario;
                escribir_log("El usuario $nom_usuario creado correctamente por el usuario: $creador_usuario", 'usuarios');
                $_SESSION['mensaje'] = "Usuario dado de alta correctamente.";
                header('Location: index.php');
                exit();
            } else {
                escribir_log("Error al crear el usuario $nom_usuario ", 'usuarios');
                $errores[] = "Hubo un problema al dar de alta el usuario.";
                header('Location: index.php');
            }
        } catch (PDOException $e) {
            $errores[] = "Error al insertar los datos: " . $e->getMessage();
        }
    }

    // Almacenar errores en la sesión para mostrarlos
    $_SESSION['errores'] = $errores;
}
?>

<?php
//CABECERA
include_once "includes/header.php";
?>
<!-- Contenedor principal de la página -->
<div class="container-fluid d-flex flex-column min-vh-100 bg-light">
    <div class="row flex-grow-1 justify-content-center">
        <!-- Formulario de registro -->
        <main class="col-md-8 col-lg-6 p-4">
            <!-- Muestra errores, si los hay -->
            <?php require_once('config/procesa_errores.php'); ?>
            <h2 class="text-center mb-4">Formulario de Nuevo Usuario</h2>
            <!-- Formulario -->
            <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="mt-4  border border-dark rounded p-4">
                <!-- Datos personales -->
                <h4 class="mb-3">Datos personales</h4>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="dni" class="form-label">DNI:</label>
                        <input type="text" id="dni" name="dni" class="form-control" pattern="[0-9]{8}[A-Za-z]{1}" title="Debe poner 8 números y una letra." maxlength="9" placeholder="Ej: 12345678A" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nombre" class="form-label">Nombre:</label>
                        <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Nombre" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="apellidos" class="form-label">Apellidos:</label>
                        <input type="text" id="apellidos" name="apellidos" class="form-control" placeholder="Apellidos" required>
                    </div>
                </div>
                <h4 class="mb-3">Contacto</h4>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="telefono" class="form-label">Teléfono:</label>
                        <input type="tel" id="telefono" name="telefono" class="form-control" pattern="[0-9]{9}" placeholder="Teléfono móvil" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Correo electrónico:</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="ejemplo@dominio.com" required>
                    </div>
                </div>

                <h4 class="mb-3">Contraseña</h4>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="clave" class="form-label">Contraseña:</label>
                        <input type="password" id="clave" name="clave" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="confirmar_clave" class="form-label">Confirmar contraseña:</label>
                        <input type="password" id="confirmar_clave" name="confirmar_clave" class="form-control" required>
                    </div>
                </div>
                <!-- FIN Formulario -->
                <!-- Botones -->
                <div class="d-flex justify-content-between mt-4">
                    <button type="submit" class="btn btn-primary" name="agregar_usuario">Registrar usuario</button>
                    <a href="javascript:history.back()" class="btn btn-secondary">Volver</a>
                </div>
            </form>
        </main>
    </div>
</div>

<!-- Footer -->
<?php include_once "includes/footer.php" ?>