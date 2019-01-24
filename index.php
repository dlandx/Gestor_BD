<?php 
    // Cargamos los fichero '.php' que se van a utilizar...
    spl_autoload_register(function ($clase) {
        require "$clase.php";
    });

    session_start(); // Crear o Abrir sesión... 
    $viewTables = false; // Ver el contenido de las tablas...  
    $info = "";
    
    // Acciones al pulsar un BTN...
    switch (filter_input(INPUT_POST, 'btn')) {
        case "Conectar":
            // Obtenemos los datos de la sesión para la conexión BBDD...
            $_SESSION['conexion']['host'] = filter_input(INPUT_POST, 'host');
            $_SESSION['conexion']['user'] = filter_input(INPUT_POST, 'user');
            $_SESSION['conexion']['pass'] = filter_input(INPUT_POST, 'pass');
            
            // Instanciar clase BBDD.php -> contendra la Conexión con la BBDD...
            $bd = new BBDD($_SESSION['conexion']);
            if ($bd->getInfo() === true){ // Conexión satisfactoria... 
                $viewTables = true; // Ver las BBDD del host en HTML - CHECKBOX...
                $tablasBD = $bd->getDatosBD("SHOW DATABASES"); // Obtener las BBDD del host
                
                // Instanciar clase View.php -> Presentación de datos del modelo (HTML)
                $view = new View();
                $html_checkBD = $view->viewBD($tablasBD); // Obtener las BBDD del HOST
            }
            break;

        case "Gestionar":
            // Obtenemos la BBDD seleccionada...
            $_SESSION['conexion']['bd'] =  filter_input(INPUT_POST, 'bd_host');
            // Si no a seleccionado un check...
            $info = ($_SESSION['conexion']['bd'] === null) ? "Seleccione una BBDD" : header("Location: tablas.php");
            
            // Instanciar clase BBDD.php -> contendra la Conexión con la BBDD...
            $bd = new BBDD($_SESSION['conexion']);
            if ($bd->getInfo() === true){ // Conexión satisfactoria... 
                $viewTables = true; // Ver las BBDD del host en HTML - CHECKBOX...
                $tablasBD = $bd->getDatosBD("SHOW DATABASES"); // Obtener las BBDD del host
                
                // Instanciar clase View.php -> Presentación de datos del modelo (HTML)
                $view = new View();
                $html_checkBD = $view->viewBD($tablasBD); // Obtener las BBDD del HOST
            }
            break;
        
        case "Volver":
            // Pulsar el BTN Volver - clase tablas.php -> Mostramos las BBDD para seleccionar otra...
            // Instanciar clase BBDD.php -> contendra la Conexión con la BBDD...
            $bd = new BBDD($_SESSION['conexion']);
            if ($bd->getInfo() === true){ // Conexión satisfactoria... 
                $viewTables = true; // Ver las BBDD del host en HTML - CHECKBOX...
                $tablasBD = $bd->getDatosBD("SHOW DATABASES"); // Obtener las BBDD del host
                
                // Instanciar clase View.php -> Presentación de datos del modelo (HTML)
                $view = new View();
                $html_checkBD = $view->viewBD($tablasBD); // Obtener las BBDD del HOST
            }
            break;
            
        default:
            break;
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Bases de Datos del Host</title>
    </head>
    <body>
        <div>
            <span><?=$info?></span>
            <h1>Gestor de Base de Datos</h1>
            
            <fieldset>
                <legend>Datos de conexión</legend>
                
                <form action="index.php" method="POST">
                    <div class="filds">
                        <input type="text" placeholder="Ingresar nombre del host" name="host" value="<?=$_SESSION['conexion']['host'] ?? null?>">
                        <label for="">Nombre del Host</label>
                    </div>
                    
                    <div class="filds">
                        <input type="text" placeholder="Ingresar nombre de usuario" name="user" value="<?=$_SESSION['conexion']['user'] ?? null?>">
                        <label for="">Nombre de usuario</label>
                    </div>
                    
                    <div class="filds">
                        <input type="password" placeholder="Ingresar contraseña" name="pass" value="<?=$_SESSION['conexion']['pass'] ?? null?>">
                        <label for="">Password</label>
                    </div>
                    
                    <input type="submit" id="success" value="Conectar" name="btn">
                </form>                
            </fieldset>
        </div>
        
        <?php if ($viewTables):?>
            <div>
                <fieldset>
                   <legend>Base de datos del Host [<?=$_SESSION['conexion']['host']?>]</legend>

                   <form action="index.php" method="POST">
                       <?=$html_checkBD?>
                       <input type="submit" id="success" value="Gestionar" name="btn">
                   </form>                
               </fieldset>
            </div>
        <?php endif; ?>
    </body>
</html>
