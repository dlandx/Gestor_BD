<?php
// Cargamos los fichero '.php' que se van a utilizar...
    spl_autoload_register(function ($clase) {
        require "$clase.php";
    });
    session_start(); // Crear o Abrir sesión...
    $info = "";
    // Unserialize(...) -> Restaurar los valores originales del array serializado = Obtenemos los datos
    //$datos = unserialize($_GET['key']); // Obtenemos ARRAY enviado por GET gestionarTabla.php (BTN - Editar)...
    
    $tipo = unserialize($_GET['key']); // Obtenemos ARRAY enviado por GET gestionarTabla.php (BTN - Editar)...
    // unserialize(...) -> Return TRUE si los datos son serializados, FALSE si no esta serializado...
    if($tipo === false ){ // Dato nombre TABLA
        echo "TABLA";
        $datos = $tipo;
        $isSerialize = false;
        $btn = "Insertar";
    } else { // Dato serializado ARRAY
        echo "SERIALIZE";
        $datos = $tipo;
        $isSerialize = true;
        $btn = "Actualizar";
    }
    
    // Instanciar clase BBDD.php -> contendra la Conexión con la BBDD...
    $bd = new BBDD($_SESSION['conexion']);
    if ($bd->getInfo() === true){ // Conexión satisfactoria...
        $nameColumnBD = $bd->nameColumnTable($_SESSION['tabla']); // Nombre de las columnas de la TABLA BD...
        $identiyPK = $bd->getIdentifyTable(); // Obtener identificadores de la TABLA como la PK, FK, Multi-Key...

        // Instanciar clase View.php -> Presentación de datos del modelo (HTML)
        $view = new View();
        //$html_form = $view->editTableForm($nameColumnBD, $identiyPK, $datos);
        $html_form = ($isSerialize)? $view->editTableForm($nameColumnBD, $identiyPK, $datos) : $view->insertTableForm($nameColumnBD, $identiyPK, $datos);

        // Acciones al pulsar un BTN...
        switch (filter_input(INPUT_POST, 'btn')) {
            case "Actualizar":
                // Actualizamos la TUPLA, obtenemos los nuevos valores insertados en los campos...
                $datos = filter_input(INPUT_POST, 'new', FILTER_DEFAULT, FILTER_FORCE_ARRAY);
                // Realizamos parte del CRUD -> UPDATE
                $result = $bd->update($_SESSION['tabla'], $nameColumnBD, $identiyPK, $datos);
                ($result === true) ? "Location: gestionarTabla.php" : $info = "Se produjo un error";
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
                ($result === true) ? "Location: gestionarTabla.php" : $info = "Se produjo un error";
                break;
            
            default:
                break;
        }
        
        // CONTROLAR AL PULSAR BTN recarga pagina y LINE 9 sin datos...
    }
    
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Editar datos Tabla</title>
    </head>
    <body>
        <div class="content">
            <h1>Ingrese los nuevos datos para editar el producto</h1>
            
            <fieldset>
                <legend>Modificar Producto</legend>
                
                <span>Si el campo no se puede editar es un identificador de la tabla [PK, FK]</span>
                <form action="editar.php" method="POST">
                    <?=$html_form?>
                    <!-- BTN -->
                    <input type="submit" id="success" value="<?=$btn?>" name="btn">
                    <input type="submit" id="success" value="Cancelar" name="btn">
                </form>
            </fieldset>
        </div>
    </body>
</html>

