<?php
    // Cargamos los fichero '.php' que se van a utilizar...
    spl_autoload_register(function ($clase) {
        require "$clase.php";
    });
    
    session_start(); // Crear o Abrir sesión... 
    $info = $_SESSION['conexion']['bd']; // BD seleccionada

    // Instanciar clase BBDD.php -> contendra la Conexión con la BBDD...
    $bd = new BBDD($_SESSION['conexion']);
    if ($bd->getInfo() === true){ // Conexión satisfactoria... 
        $tablasBD = $bd->getDatosBD("SHOW TABLES"); // Obtener las TABLAS de la BD elegida...
        
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
    </head>
    <body>
        <div>
            <span><?=$info?></span>
            <h1>Gestionar tablas de la Base de Datos</h1>
            
            <fieldset>
                <legend>Listado de Bases de Datos</legend>
                
                <form action="index.php" method="POST">
                    <input type="submit" id="success" value="Volver" name="btn">
                </form>                
            </fieldset>
            
            <fieldset>
                <legend>Tablas de la Base de Datos</legend>
                
                <form action="gestionarTabla.php" method="POST">
                    <?=$html_btn?>
                </form>                
            </fieldset>
        </div>
    </body>
</html>
