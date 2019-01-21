<?php 
    // Cargamos los fichero '.php' que se van a utilizar...
    spl_autoload_register(function ($clase) {
        require "$clase.php";
    });

    $viewTables = false; // Ver el contenido de las tablas...    
    
    switch (filter_input(INPUT_POST, 'btn')) {
        case "Conectar":
            $host = filter_input(INPUT_POST, 'host');
            $user = filter_input(INPUT_POST, 'user');
            $pass = filter_input(INPUT_POST, 'pass');
            
            $bd = new BBDD($host, $user, $pass); // Conectar con la BBDD...
            //session_start(); // Crear sesion... 
            //$_SESSION['bbdd'] = $bd;
            
            $viewTables = true; // Ver las BBDD del host...            
            $tablasBD = $bd->getBBDD("SHOW DATABASES"); // Obtener las BBDD del host

            $view = new View();
            $html_checkBD = $view->viewBD($tablasBD);

            break;

        case "Gestionar":
            $bdSelected =  filter_input(INPUT_POST, 'bd_host');
  
            $bd->getTables("SHOW TABLES");
            
            //$view = new View();
            //$view->viewTables();
            break;
        
        default:
            break;
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <div>
            <h1>Gestor de Base de Datos</h1>
            
            <fieldset>
                <legend>Datos de conexión</legend>
                
                <form action="index.php" method="POST">
                    <div class="filds">
                        <input type="text" placeholder="Ingresar nombre del host" name="host" value="<?=$host ?? null?>">
                        <label for="">Nombre del Host</label>
                    </div>
                    
                    <div class="filds">
                        <input type="text" placeholder="Ingresar nombre de usuario" name="user" value="<?=$user ?? null?>">
                        <label for="">Nombre de usuario</label>
                    </div>
                    
                    <div class="filds">
                        <input type="password" placeholder="Ingresar contraseña" name="pass" value="<?=$pass ?? null?>">
                        <label for="">Password</label>
                    </div>
                    
                    <input type="submit" id="success" value="Conectar" name="btn">
                </form>                
            </fieldset>
        </div>
        
        <?php if ($viewTables):?>
            <div>
                <fieldset>
                   <legend>Base de datos del Host <?=$host?></legend>

                   <form action="index.php" method="POST">
                       <?=$html_checkBD?>
                       <input type="submit" id="success" value="Gestionar" name="btn">
                   </form>                
               </fieldset>
            </div>
        <?php endif; ?>
    </body>
</html>
