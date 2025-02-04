<?php
session_start();
include_once(__DIR__ . '/../config/funciones.php');
require __DIR__ . "/autoload.php";
// Clave secreta de Stripe
$stripe_clave_privada = 'sk_test_51QiKchJtRHNFpLrqrlz8wUaTzQmtmdRzfwviJyQxVVBSmVHmfgRgPR7vA3lwcWXZsTP3JbRF3SjGYM2rzW81f0S100fyf207Zt';
\Stripe\Stripe::setApiKey($stripe_clave_privada);
//Carrito desde la sesión
$carrito = $_SESSION['carrito'];

//Verifica si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $forma_pago = $_POST['forma_pago'];
    $gastos_envio = $_POST['gastos_envio'];
    $_SESSION['forma_pago'] = $forma_pago;
    $_SESSION['gastos_envio'] = $gastos_envio;
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
}
