<?php
session_start();
include_once 'config/funciones.php';
include_once 'config/conectar_db.php';
//Verificamos que el usuario esté logueado y tenga el rol adecuado
if (!isset($_SESSION['acceso']) || $_SESSION['rol'] !== 'Administrador') {
    escribir_log("Error al acceder a la zona de 'LOGS' por falta de permisos ->" . $_SESSION['usuario'], 'zonas');
    header("Location: index.php");
    exit;
}
include_once 'includes/header.php';
//Logs disponibles en un array
$archivos_log = [
    'acceso'     => 'logs/logs_acceso.txt',
    'familias'   => 'logs/logs_familias.txt',
    'pedidos'    => 'logs/logs_pedidos.txt',
    'productos'  => 'logs/logs_productos.txt',
    'stripe'     => 'logs/logs_stripe.txt',
    'subfamilias' => 'logs/logs_subfamilias.txt',
    'usuarios'   => 'logs/logs_usuarios.txt',
    'zonas'      => 'logs/logs_zonas.txt',
];

$tipo_log = $_GET['tipo_log'] ?? 'acceso';
$archivo = $archivos_log[$tipo_log] ?? 'logs/logs_zonas.txt';
?>

<div class="container-fluid py-5">
    <h1 class="text-center display-4 mb-4">LOGS</h1>
    <!-- Selector de logs -->
    <form method="GET" class="mb-4 text-center">
        <label for="tipo_log" class="form-label fs-5 fw-bold">Selecciona el log que quieres revisar:</label>
        <div class="d-flex justify-content-center align-items-center gap-2">
            <select name="tipo_log" id="tipo_log" class="form-select w-auto">
                <?php foreach ($archivos_log as $clave => $valor): ?>
                    <option value="<?= $clave ?>" <?= $clave === $tipo_log ? 'selected' : '' ?>><?= ucfirst($clave) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Ver Log</button>
        </div>
    </form>

    <!-- Mostrar el log -->
    <div class="card shadow-lg rounded-3">
        <div class="card-body bg-dark  text-white">
            <h5 class="card-title text-center mb-4">Registros del Log</h5>
            <?php
            if (file_exists($archivo)) {
                $logs = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                if ($logs) {
                    echo '<div class="log-container overflow-auto">';
                    echo '<pre class="mb-0 text-white">';
                    echo implode("\n", array_map('htmlspecialchars', $logs));
                    echo '</pre>';
                    echo '</div>';
                } else {
                    echo '<p class="text-muted">No hay registros en este log.</p>';
                }
            }
            ?>
        </div>
    </div>
</div>
</div>
<?php include_once 'includes/footer.php'; ?>