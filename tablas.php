<?php
    // Cargamos los fichero '.php' que se van a utilizar...
    spl_autoload_register(function ($clase) {
        require "$clase.php";
    });
    
    session_start(); // Crear sesion... 
    $info = $_SESSION['bd']; // BD seleccionada

    $bd = new BBDD($_SESSION['host'], $_SESSION['user'], $_SESSION['pass'], $info); // Conectar con la BBDD...
    if ($bd->getInfo() === true){ // Conexión satisfactoria... 
        $tablasBD = $bd->getTables("SHOW TABLES");
        
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
                    <input type="submit" id="success" value="Volver">
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

