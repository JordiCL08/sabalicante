<?php
function comprobar_DNI($dni, &$errores)
{
    //Comprueba la cantidad de caracteres
    if (strlen($dni) !== 9) {
        $errores[] = "DNI INCORRECTO: No tiene 9 caracteres.";
        return false;
    }

    //Comprueba el formato
    if (!preg_match('/^[0-9]{8}[A-Za-z]$/', $dni)) {
        $errores[] = "DNI INCORRECTO: Debe contener 8 números seguidos de una letra.";
        return false;
    }

    //Comprueba que la letra coincide con los números
    $numeros_DNI = substr($dni, 0, 8);
    $letra_calculada = "TRWAGMYFPDXBNJZSQVHLCKE"[$numeros_DNI % 23];

    if (strtoupper($dni[8]) !== $letra_calculada) {
        $errores[] = "DNI INCORRECTO: La letra no coincide con los números.";
        return false;
    }

    return true;
}

function comprobar_email($email, &$errores)
{
    //Comprueba el formato
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "Correo electrónico inválido: Formato incorrecto.";
        return false;
    }

    //Comprueba que no tenga espacios
    $email_trimmed = trim($email);
    if (strpos($email_trimmed, " ") !== false) {
        $errores[] = "Correo electrónico inválido: Contiene espacios.";
        return false;
    }

    //Comprueba posicion del @  y el dominio
    $posicion_arroba = strpos($email_trimmed, "@");
    $posicion_punto = strpos($email_trimmed, ".", $posicion_arroba);

    if ($posicion_arroba === false || $posicion_punto === false) {
        $errores[] = "Correo electrónico inválido: Falta el '@' o el dominio.";
        return false;
    }

    return true;
}

function obtenerColorEstadoPedido($estadoPedido) {
    switch (strtolower($estadoPedido)) {
        case 'pendiente':
            return 'warning'; 
        case 'enviado':
            return 'info'; 
        case 'entregado':
            return 'success'; 
        case 'cancelado':
            return 'danger'; 
        default:
            return 'secondary'; 
    }
}