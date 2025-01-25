<?php
include_once "includes/header.php";
// Verificamos que el usuario esté logueado y tenga el rol adecuado
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'Administrador'  && $_SESSION['rol'] !== 'Contable') {
    // Redirigimos a la página de acceso si no está logueado o no tiene el rol adecuado
    header("Location: index.php");
    exit; 
}
try {
    $pdo = conectar_db();
    $query = "SELECT id_pedido, fecha, total, id_usuario,forma_pago FROM pedidos ORDER BY fecha DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo '<div class="alert alert-danger text-center mt-4" role="alert">
            Error al obtener los pedidos: ' . htmlspecialchars($e->getMessage()) . '
          </div>';
    exit;
}
?>

<div class="container-fluid py-5">
    <div class="row mb-4">
        <div class="col">
            <h1 class="text-center display-4 mb-4">Visor de Ventas</h1>
        </div>
    </div>

    <!-- Filtros por fecha -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
            <i class="fas fa-filter"></i> Filtros
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="fechaMinima">Fecha mínima:</label>
                    <input type="date" id="fechaMinima" class="form-control form-control-lg">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="fechaMaxima">Fecha máxima:</label>
                    <input type="date" id="fechaMaxima" class="form-control form-control-lg">
                </div>
            </div>
        </div>
    </div>

    <!-- Total Filtrado -->
    <div class="d-flex justify-content-end mb-3">
        <h4 class="text-success">Ventas Totales: <span id="totalFiltrado">0.00</span> €</h4>
    </div>

    <!-- Tabla de ventas -->
    <div class="table-responsive">
        <table id="tabla-ventas" class="table table-striped table-hover table-bordered shadow-sm">
            <thead class="table-dark">
                <tr>
                    <th>ID Pedido</th>
                    <th>Fecha</th>
                    <th>Forma de Pago</th>
                    <th>Total</th>
                    <th>Usuario</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($pedidos)): ?>
                    <?php foreach ($pedidos as $pedido): ?>
                        <?php
                        $gestorUsuarios = new GestorUsuarios($pdo);
                        $usuario = $gestorUsuarios->obtener_usuario_por_id($pedido['id_usuario']); //sacamos los datos del usuario por el id
                        if (is_object($usuario)) { //extraemos el dato que queremos del objeto
                            $usuario = $usuario->getEmail();
                        }
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($pedido['id_pedido']) ?></td>
                            <td><?= htmlspecialchars($pedido['fecha']) ?></td>
                            <td><?= htmlspecialchars($pedido['forma_pago']) ?> </td>
                            <td><?= number_format($pedido['total'], 2) ?> €</td>
                            <td><?= $usuario ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">No hay ventas registradas.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Inicializar DataTables
        var table = $('#tabla-ventas').DataTable({
            "order": [
                [1, "desc"]
            ],
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            }
        });

        // Filtro por fechas
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            var min = $('#fechaMinima').val();
            var max = $('#fechaMaxima').val();
            var fecha = data[1];

            if (
                (min === "" || fecha >= min) &&
                (max === "" || fecha <= max + "T23:59:59")
            ) {
                return true;
            }
            return false;
        });

        // Calcular el total filtrado
        function calcularTotalFiltrado() {
            var total = 0;

            // Recorremos las filas visibles de la tabla
            table.rows({
                search: 'applied'
            }).every(function() {
                var data = this.data(); // Datos de la fila actual

                //En la columna 3
                var totalPedido = data[3]
                    .replace('€', '') // Eliminamos el símbolo de euro
                    .replace(',', '') // Eliminamos posibles comas (formato europeo)
                    .trim(); //Quitamos espacios 
                //Convertimos el texto a número y verificamos si es válido
                totalPedido = parseFloat(totalPedido);
                if (!isNaN(totalPedido)) {
                    total += totalPedido; //Sumamos
                }
            });
            //Total con dos decimales
            $('#totalFiltrado').text(total.toFixed(2));
        }

        //Se aplican los cambios de fechas y se recalcula el total
        $('#fechaMinima, #fechaMaxima').on('change', function() {
            table.draw();
            calcularTotalFiltrado();
        });

        //Tras cambiar filtros se recalcula el total
        table.on('draw', function() {
            calcularTotalFiltrado();
        });
    });
</script>

<!-- Footer -->
<?php include_once "includes/footer.php"; ?>