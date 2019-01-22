<?php
    // Cargamos los fichero '.php' que se van a utilizar...
    spl_autoload_register(function ($clase) {
        require "$clase.php";
    });
    
    session_start(); // Crear sesion... 
    $info = $_SESSION['bd']; // BD seleccionada
    
    $tablaBD = filter_input(INPUT_POST, 'tablas_bd');
    
    $bd = new BBDD($_SESSION['host'], $_SESSION['user'], $_SESSION['pass'], $info); // Conectar con la BBDD...
    if ($bd->getInfo() === true){ // Conexión satisfactoria... 
        $nameColumnBD = $bd->nombres_campos($tablaBD); // Nombre de las columnas de la tabla BD.
        $sql = "SELECT * FROM $tablaBD";
        $tuplas = $bd->getBBDD($sql);
        //var_dump($tuplas);
        
        $view = new View();
        $html_thead = $view->tableHead($nameColumnBD ?? []);
        $html_tbody = $view->tableBody($tuplas);
        
        // Problema tbody -> array 
        // SELECT * FROM familia WHERE cod LIKE 'E%' OR nombre LIKE 'E%'
    }
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Gestionar Tabla</title>
    </head>
    <body>
        <div>
            <span><?=$info?></span>
            <h1>Gestionar registros de la tabla</h1>
            
            <fieldset>
                <legend>Registros de la tabla <?=$tabla?></legend>
                
                <table>
                    <?php 
                        echo $html_thead;
                        echo $html_tbody ?? null;
                    ?>
                </table>
                
                <form action="index.php" method="POST">
                    <input type="submit" id="success" value="Volver">
                </form>                
            </fieldset>
        </div>
    </body>
</html>
