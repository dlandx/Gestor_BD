<?php
    // Cargamos los fichero '.php' que se van a utilizar...
    spl_autoload_register(function ($clase) {
        require "$clase.php";
    });
    
    session_start(); // Crear o Abrir sesión...
    $info = $_SESSION['conexion']['bd']; // BD seleccionada
    // Si no existe la SESSION creo para TABLA seleccionada...
    if(!isset($_SESSION['tabla'])){
        $_SESSION['tabla'] = filter_input(INPUT_POST, 'tablas_bd'); // Obtener la TABLA seleccionada
    }
    
    // Instanciar clase BBDD.php -> contendra la Conexión con la BBDD...
    $bd = new BBDD($_SESSION['conexion']);
    if ($bd->getInfo() === true){ // Conexión satisfactoria...
        $nameColumnBD = $bd->nameColumnTable($_SESSION['tabla']); // Nombre de las columnas de la TABLA BD...
        $tuplas = $bd->getDatosBD("SELECT * FROM {$_SESSION['tabla']}"); // Obtener los datos de la TABLA BD...
        $identiyPK = $bd->getIdentifyTable(); // Obtener identificadores que tenga la TABLA como la PK, FK, Multi-Key...
        
        // Instanciar clase View.php -> Presentación de datos del modelo (HTML)
        $view = new View();
        $html_thead = $view->tableHead($nameColumnBD); // Tabla head (Titulo)
        $html_tbody = $view->tableBody($nameColumnBD, $tuplas); // Tabla body (Contenido)
    }
        
    // Acciones al pulsar un BTN...
    switch (filter_input(INPUT_POST, 'btn')) {
        case "Editar":
            $datoTupla = filter_input(INPUT_POST, 'celda', FILTER_DEFAULT, FILTER_FORCE_ARRAY);
            $datos = serialize($datoTupla); // CONTROLAR ESCAPE CHART...
            header("Location: editar.php?key=$datos");
            break;
        
        case "Eliminar":
            break;
        
        case "Insertar":

            break;
        
        case "Volver":
            // Elinamos la sesión que almacenaba la TABLA seleccionada de la BD...
            unset($_SESSION['tabla']); // Para seleccionar otra...
            header("Location: tablas.php");
            exit();
            break;
        
        default:
            break;
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
                <legend>Registros de la tabla <?=$_SESSION['tabla']?></legend>
                
                <form action="gestionarTabla.php" method="POST">
                    <table>
                        <?php 
                            echo $html_thead;
                            echo $html_tbody ?? null;
                        ?>
                    </table>
                    <input type="submit" id="success" value="Insertar" name="btn">
                    <input type="submit" id="success" value="Volver" name="btn">
                </form>                
            </fieldset>
        </div>
    </body>
</html>
