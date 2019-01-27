<?php
    // Cargamos los fichero '.php' que se van a utilizar...
    spl_autoload_register(function ($clase) {
        require "$clase.php";
    });
    session_start(); // Crear o Abrir sesión...
    $info = "Base de Datos - {$_SESSION['conexion']['bd']} (Host - {$_SESSION['conexion']['host']})";

    // Unserialize(...) -> Restaurar los valores originales del array serializado = Obtenemos los datos
    $tipo = unserialize(filter_input(INPUT_GET, 'key')); // Obtenemos ARRAY enviado por GET gestionarTabla.php (BTN - Editar)...
    // unserialize(...) -> Return TRUE si los datos son serializados, FALSE si no esta serializado...
    if($tipo === false ){ // Dato nombre TABLA -> INSERTAR
        $datos = $tipo;
        $isSerialize = false;
        $btn = "Insertar";
    } else { // Dato serializado ARRAY -> EDITAR
        $datos = $tipo;
        $isSerialize = true;
        $btn = "Actualizar";
    }
    
    // Instanciar clase BBDD.php -> contendra la Conexión con la BBDD...
    $bd = new BBDD($_SESSION['conexion']);
    if ($bd->getInfo() === true){ // Conexión satisfactoria...
        $nameColumnBD = $bd->nameColumnTable($_SESSION['tabla']); // Nombre de las columnas de la TABLA BD...
        $identiyPK = $bd->getIdentifyTable(); // Obtener identificadores de la TABLA como la PK, FK, Multi-Key...
        $bd->close(); // Cerrar conexión BBDD...

        // Instanciar clase View.php -> Presentación de datos del modelo (HTML)
        $view = new View();
        // Si venimos del BTN Editar -> editamos datos. BTN Insertar -> Insertar datos...
        $html_form = ($isSerialize)? $view->editTableForm($nameColumnBD, $identiyPK, $datos) : $view->insertTableForm($nameColumnBD, $identiyPK, $datos);

        // Acciones al pulsar un BTN...
        switch (filter_input(INPUT_POST, 'btn')) {
            case "Actualizar":
                // Actualizamos la TUPLA, obtenemos los nuevos valores insertados en los campos...
                $datos = filter_input(INPUT_POST, 'new', FILTER_DEFAULT, FILTER_FORCE_ARRAY);
                // Realizamos parte del CRUD -> UPDATE
                $result = $bd->update($_SESSION['tabla'], $nameColumnBD, $identiyPK, $datos);
                $info = ($result === true) ? header("Location: gestionarTabla.php") : $bd->getInfo();
                $bd->close(); // Cerrar conexión BBDD...
                break;

            case "Cancelar":
                header("Location: gestionarTabla.php");
                exit();
                break;

            case "Insertar":
                // Insertamos la TUPLA en la TABLA - BD, obtenemos los valores insertados en los campos...
                $datos = filter_input(INPUT_POST, 'new', FILTER_DEFAULT, FILTER_FORCE_ARRAY);
                // Realizamos parte del CRUD -> INSERT
                $result = $bd->insert($_SESSION['tabla'], $datos);
                $info = ($result === true) ? header("Location: gestionarTabla.php") : $bd->getInfo();
                $bd->close(); // Cerrar conexión BBDD...
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
        <title>Editar datos Tabla</title>
        <link rel="stylesheet" type="text/css" href="style.css">
    </head>
    <body>
        <div class="info">
            <h1>Ingrese los datos para realizar la operación en la tabla <b><?=$_SESSION['tabla']?></b></h1>
            <hr>
            <h4><?=$info?></h4>
        </div>
        
        <div class="content">
            <h2>Datos a ingresar</h2>
            <p>Campo resaltado es un identificador de la tabla [PK, FK, Multi-Key]</p>
            
            <form action="" method="POST">
                <div class="inputs">
                    <?=$html_form?>
                </div>
                
                <!-- BTN -->
                <div class="btn">
                    <input type="submit" class="success" value="<?=$btn?>" name="btn">
                    <input type="submit" class="danger" value="Cancelar" name="btn">
                </div>
            </form>

        </div>
    </body>
</html>

