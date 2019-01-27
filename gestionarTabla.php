<?php
    // Cargamos los fichero '.php' que se van a utilizar...
    spl_autoload_register(function ($clase) {
        require "$clase.php";
    });
    
    session_start(); // Crear o Abrir sesión...
    $info = "Base de Datos - {$_SESSION['conexion']['bd']} (Host - {$_SESSION['conexion']['host']})"; // BD seleccionada
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
        $bd->close(); // Cerrar conexión BBDD...
        
        // Instanciar clase View.php -> Presentación de datos del modelo (HTML)
        $view = new View();
        $html_thead = $view->tableHead($nameColumnBD); // Tabla head (Titulo)
        $html_tbody = $view->tableBody($nameColumnBD, $tuplas); // Tabla body (Contenido)
        
        // Acciones al pulsar un BTN...
        switch (filter_input(INPUT_POST, 'btn')) {
            case "Editar":
                $datoTupla = filter_input(INPUT_POST, 'celda', FILTER_DEFAULT, FILTER_FORCE_ARRAY);
                $datos = serialize($datoTupla); // CONTROLAR ESCAPE CHART QUE VIENE del input...
                header("Location: editar.php?key=$datos");
                break;

            case "Eliminar":
                $datos = filter_input(INPUT_POST, 'celda', FILTER_DEFAULT, FILTER_FORCE_ARRAY);
                $result = $bd->delete($_SESSION['tabla'], $datos); // Controlar que RECARGE LA PAGINA
                $info = ($result === true) ? header("Location: gestionarTabla.php") : $bd->getInfo();;
                $bd->close(); // Cerrar conexión BBDD...
                break;

            case "Insertar":
                header("Location: editar.php?key={$_SESSION['tabla']}");
                break;

            case "Volver":
                // Eliminamos la sesión que almacenaba la TABLA seleccionada de la BD...
                unset($_SESSION['tabla']); // Para seleccionar otra...
                header("Location: tablas.php");
                exit();
                break;

            default:
                break;
        } // Switch - BTN
        
    }
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Gestionar Tabla</title>
        <link rel="stylesheet" type="text/css" href="style.css">        
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    </head>
    <body>
        <div class="info">
            <h1>Gestionar registros de la tabla <b><?=$_SESSION['tabla']?></b></h1>
            <hr>
            <h4><?=$info?></h4>
        </div>
        
        <div class="content">
            <h2>Registros de la tabla</h2>
            <p>Si por algún casual se corta el contenido (Descomentar en CSS: div - class="content" - position: absolute;)</p>
            <form action="gestionarTabla.php" method="POST">
                <table>
                    <?php 
                        echo $html_thead;
                        echo $html_tbody ?? null;
                    ?>
                </table>
                <div class="btn">
                    <input type="submit" class="success" value="Insertar" name="btn">
                    <input type="submit" class="danger" value="Volver" name="btn">
                </div>
            </form>                

        </div>
    </body>
</html>