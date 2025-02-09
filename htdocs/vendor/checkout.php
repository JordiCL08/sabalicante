<?php
session_start();
include_once(__DIR__ . '/../gestores/gestor_usuarios.php');
include_once(__DIR__ . '/../gestores/gestor_pedidos.php');
include_once(__DIR__ . '/../config/funciones.php');
include_once(__DIR__ . '/../config/conectar_db.php');
require __DIR__ . "/autoload.php";
// Clave secreta de Stripe
$stripe_clave_privada = 'sk_test_51QiKchJtRHNFpLrqrlz8wUaTzQmtmdRzfwviJyQxVVBSmVHmfgRgPR7vA3lwcWXZsTP3JbRF3SjGYM2rzW81f0S100fyf207Zt';
\Stripe\Stripe::setApiKey($stripe_clave_privada);
//Conexión
$pdo = conectar_db();
$gestorUsuarios = new GestorUsuarios($pdo);
$gestorPedido = new GestorPedidos($pdo);
$ID_usuario = $_SESSION['id'];
$usuarioDetalles = $gestorUsuarios->obtener_usuario_por_id($ID_usuario);
//INTRODUCIMOS LOS DATOS DE ENVIO/FACTURACION QUE SACAMOS DEL FORMULARIO DE PAGO(EN CASO DE QUE NO TENGA DATOS DE ENVIO O SE CAMBIEN)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //datos de envio
    if ($_POST['direccion']) {
        $direccion = $_POST['direccion'];
        $provincia = $_POST['provincia'];
        $localidad = $_POST['localidad'];
        $cp = $_POST['cp'];
        //seteamos los datos
        $usuarioDetalles->setDireccion($direccion);
        $usuarioDetalles->setProvincia($provincia);
        $usuarioDetalles->setLocalidad($localidad);
        $usuarioDetalles->setCp($cp);
        //Actualizamos los datos de envio del usuario
        $gestorUsuarios->editar_usuario($usuarioDetalles);
    }
    //FORMA DE PAGO
    $forma_pago = $_POST['forma_pago'];
    $gastos_envio = $_POST['gastos_envio'];
    $_SESSION['forma_pago'] = $forma_pago;
    $_SESSION['gastos_envio'] = $gastos_envio;

    //Iniciamos la transaccion
    $pdo->beginTransaction();
    try {
        //Carrito desde la sesión
        $carrito = $_SESSION['carrito'];
        // Verificar y descontar stock
        foreach ($carrito as $producto) {
            $stock = $gestorPedido->stock_actual($producto['codigo']);
            if ($gestorPedido->verificar_stock($producto['codigo'], $producto['cantidad'])) {
                $gestorPedido->descontar_stock($producto['codigo'], $producto['cantidad']);
            } else {
                // Si el stock no es suficiente, revertir la transacción 
                $pdo->rollBack();
                $stock_actual = $stock['stock'];
                escribir_log("Error con el pedido: Realizado por el usuario: " . $_SESSION['usuario'] . " por falta de stock en el producto: " . $producto['nombre'], 'pedidos');
                $_SESSION['errores'][] = "No hay suficiente stock del producto " . $producto['nombre'] . ". El Stock actual es de: " . $stock_actual;
                header("Location: ../carrito.php");
                exit();
            }
        }
        $pdo->commit();
        //Verifica que el total esté en la sesión
        if (isset($_SESSION['total']) && $_SESSION['total'] > 0) {
            $line_items = []; //Array para la linea de productos

            foreach ($carrito as $producto) {
                //Pasamos el precio de cada producto en centimos para que lo pueda leer stripe
                $precio_final_en_centimos = round($producto['precio_final'] * 100);
                //Añadimos productos
                $line_items[] = [
                    'quantity' => $producto['cantidad'],
                    'price_data' => [
                        'currency' => 'eur',
                        'unit_amount' => $precio_final_en_centimos,
                        'product_data' => [
                            'name' => $producto['nombre'],
                        ],
                    ],
                ];
            }
            //Mostrar en Stripe los gastos de envio en caso de que los haya 
            if ($_SESSION['gastos_envio'] > 0) {
                $gastos_envio_en_centimos = round($_SESSION['gastos_envio'] * 100);
                $line_items[] = [
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => 'eur',
                        'unit_amount' => $gastos_envio_en_centimos,
                        'product_data' => [
                            'name' => 'Gastos de Envío',
                        ],
                    ],
                ];
            }

            if ($forma_pago === 'tarjeta') {
                try {
                    // Crear la sesión de Checkout de Stripe
                    $checkout_session = \Stripe\Checkout\Session::create([
                        'payment_method_types' => ['card', 'paypal'],
                        'line_items' => $line_items, //Productos y cantidades
                        'mode' => 'payment',
                        'success_url' => 'https://sabalicante.wuaze.com/carrito/pedido_completado.php',
                        'cancel_url' => 'https://sabalicante.wuaze.com/carrito.php',
                    ]);
                    escribir_log("El pago del pedido ha pasado por stripe", 'stripe');
                    //Redirigir al cliente al checkout de Stripe
                    header("Location: " . $checkout_session->url);
                    exit;
                } catch (\Stripe\Exception\ApiErrorException $e) {
                    escribir_log("El pago del pedido no ha pasado por stripe", 'stripe');
                    echo "Error de API de Stripe: " . $e->getMessage();
                } catch (Exception $e) {
                    escribir_log("Error general de stripe", 'stripe');
                    echo "Error general: " . $e->getMessage();
                }
            } else {
                //Cuando el pago no es en tarjeta o paypal
                header("Location: ../carrito/pedido_completado.php");
                exit;
            }
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        escribir_log("Error con el pedido = ID Pedido: $pedido_id realizado por el usuario: " . $_SESSION['usuario'], 'pedidos');
        $_SESSION['errores'][] =  "Error al realizar el pedido. Por favor, intente nuevamente más tarde.";
    }
}
