<?php
    // Cargamos los fichero '.php' que se van a utilizar...
    spl_autoload_register(function ($clase) {
        require "$clase.php";
    });
    
    session_start(); // Crear o Abrir sesión... 
    $info = "Base de Datos - {$_SESSION['conexion']['bd']} (Host - {$_SESSION['conexion']['host']})"; // BD seleccionada

    // Instanciar clase BBDD.php -> contendra la Conexión con la BBDD...
    $bd = new BBDD($_SESSION['conexion']);
    if ($bd->getInfo() === true){ // Conexión satisfactoria... 
        $tablasBD = $bd->getDatosBD("SHOW TABLES"); // Obtener las TABLAS de la BD elegida...
        $bd->close(); // Cerrar conexión BBDD...
        // Instanciar clase View.php -> Presentación de datos del modelo (HTML)
        $view = new View();
        $html_btn = $view->viewTables($tablasBD);
    }
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Tablas de la Base de Datos</title>
        <link rel="stylesheet" type="text/css" href="style.css">
    </head>
    <body>
        <div class="info">
            <h1>Gestionar tablas de la Base de Datos</h1>
            <hr>
            <h4><?=$info?></h4>
        </div>
           
        <div class="volver">
            <h3>Volver a listar las BBDD - <b><?=$_SESSION['conexion']['host']?></b></h3>
            <form action="index.php" method="POST">
                <input type="submit" class="danger" value="Volver" name="btn">
            </form>                
        </div>
          
        <div class="content">
            <h2>Tablas de la Base de Datos</h2>
            <p>Si por algún casual se corta el contenido (Descomentar en CSS: div - class="content" - position: absolute;)</p>
            <form action="gestionarTabla.php" method="POST">
                <div class="tablasBD">
                    <?=$html_btn?>
                </div>
            </form> 
        </div> 
    </body>
</html>
