<?php
/*+++++++++++++++++++++++++++++++++++++++++
   CONEXIÓN CON LA BASE DE DATOS
 +++++++++++++++++++++++++++++++++++++++++*/

function conectar_db()
{
  $hostname  = "localhost";
  $usuario_db = "administrador";
  $pass_db = "";
  $db = "if0_37973440_sabalic";

  try {
    //CONFIGURAMOS DSN
    $dsn = "mysql:host=$hostname;dbname=$db";
    //CREAMOS LA INSTANCIA
    $pdo = new PDO($dsn, $usuario_db, $pass_db);
    //AGREGAR SETATRIBUTES DE MANERA GLOBAL
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
  } catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
    return null;
  }
}
