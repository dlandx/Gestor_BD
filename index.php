<?php 
    // Cargamos los fichero '.php' que se van a utilizar...
    spl_autoload_register(function ($clase) {
        require "$clase.php";
    });

    session_start(); // Crear sesion... 
    $viewTables = false; // Ver el contenido de las tablas...  
    $info = "";
    
    switch (filter_input(INPUT_POST, 'btn')) {
        case "Conectar":
            $_SESSION['host'] = filter_input(INPUT_POST, 'host');
            $_SESSION['user'] = filter_input(INPUT_POST, 'user');
            $_SESSION['pass'] = filter_input(INPUT_POST, 'pass');
            
            $bd = new BBDD($_SESSION['host'], $_SESSION['user'], $_SESSION['pass']); // Conectar con la BBDD...
            if ($bd->getInfo() === true){ // Conexión satisfactoria... 
                $viewTables = true; // Ver las BBDD del host...            
                $tablasBD = $bd->getBBDD("SHOW DATABASES"); // Obtener las BBDD del host

                $view = new View();
                $html_checkBD = $view->viewBD($tablasBD);
            }
            break;

        case "Gestionar":
            $_SESSION['bd'] =  filter_input(INPUT_POST, 'bd_host');
            // Si no a seleccionado un check...
            $info = ($_SESSION['bd'] === null) ? "Seleccione una BBDD" : header("Location: tablas.php");
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
                        <input type="text" placeholder="Ingresar nombre del host" name="host" value="<?=$_SESSION['host'] ?? null?>">
                        <label for="">Nombre del Host</label>
                    </div>
                    
                    <div class="filds">
                        <input type="text" placeholder="Ingresar nombre de usuario" name="user" value="<?=$_SESSION['user'] ?? null?>">
                        <label for="">Nombre de usuario</label>
                    </div>
                    
                    <div class="filds">
                        <input type="password" placeholder="Ingresar contraseña" name="pass" value="<?=$_SESSION['pass'] ?? null?>">
                        <label for="">Password</label>
                    </div>
                    
                    <input type="submit" id="success" value="Conectar" name="btn">
                </form>                
            </fieldset>
        </div>
        
        <?php if ($viewTables):?>
            <div>
                <fieldset>
                   <legend>Base de datos del Host [<?=$_SESSION['host']?>]</legend>

                   <form action="index.php" method="POST">
                       <?=$html_checkBD?>
                       <input type="submit" id="success" value="Gestionar" name="btn">
                   </form>                
               </fieldset>
            </div>
        <?php endif; ?>
    </body>
</html>
