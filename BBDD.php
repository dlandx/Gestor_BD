<?php

class BBDD {
    
    private $con;
    private $error;
    private $host;
    private $user ;
    private $pass;
    private $bd;
    
    public function __construct(string $h="172.17.0.2", string $u="root", string $p="root", string $bd=null) {
        $this->host = $h;
        $this->user = $u;
        $this->pass = $p;
        $this->bd = $bd;
        $this->con = $this->conexion();
        //echo $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->error = ($this->con) ? true : false;
    }
    
    /**
     * Función conecta con la BBDD, en caso de NO conexión informar del error...
     */
    private function conexion() {     
        try {
            $dsn = "mysql:host=$this->host; dbname=$this->bd";
            $atributos = [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", 
                PDO::ATTR_ERRMODE => true, // True o false da igual si esta BIEN... 
                PDO::ERRMODE_EXCEPTION => true]; // Errores para Excepciones (Solo para desarrollo)...
            
            $con = new PDO($dsn, $this->user, $this->pass, $atributos);            
            //$error = ($con) ? "conexión realizada satisfactoriamente" : "Ohhhh!!!! no se ha ha podido conectar";
        } catch (PDOException $ex) {
            die ("Se produjo un error en la conexion: ".$ex->getMessage());
        } // try
        
        return $con;
    }
    
    /**
     * Función cierra la conexión con la Base de Datos... 
     */
    public function close() {
        $this->con = null;
    }
    
    public function getInfo() {
        return $this->error;
    }


    // Obtener las BD del hosts
    public function getBBDD($sql){
        $bd = [];
        // Si se pierde la conexion, volvemos a conectar...
        if ($this->con == null) {
            $this->con = $this->conexion();
        }
        
        // Preparar sentencia...
        $result = $this->con->query($sql);
        while ($datos = $result->fetch(PDO::FETCH_NUM)) {
            //$bd[] = $datos;
            $bd[] = $datos;
        }
        return $bd;
    }
    
    // Obtener las Tablas de la BD seleccionada...
    public function getTables($sql) {
        $bd = [];
        // Si se pierde la conexion, volvemos a conectar...
        if ($this->con == null) {
            $this->con = $this->conexion();
        }
        
        // Preparar sentencia...
        $result = $this->con->query($sql);
        while ($datos = $result->fetch(PDO::FETCH_NUM)) {
            $bd[] = $datos;
        }
        return $bd;            
    }
    
    // Obtener datos de la Tabla BD
    /**
     * Función obtiene los nombres de las columnas de la BBDD...
     * @param string $tabla, tabla de la BBDD a consultar...
     * @return array, Retorna en un vector los nombres de las columnas BBDD...
     */
    public function nombres_campos(string $tabla): array {
        $campos = [];
        // Si se pierde la conexion, volvemos a conectar...
        if ($this->con == null) {
            $this->con = $this->conexion();
        }
        
        // Preparar la consulta SQL...
        $consulta = "SELECT * FROM $tabla";
        $r = $this->con->query($consulta);

        $columns = $r->columnCount(); // Devuelve el número de columnas de un conjunto de resultados
        $cont = 0; $identificador = null;
        while ($cont < $columns) {
            // Array de objetos de cada columna [tipo=VAR_STRING, flags=(not null, PK), long, tabla...]
            $meta = $r->getColumnMeta($cont); //Devuelve metadatos de una columna de un conjunto de resultados
            //$campos[] = $meta['name']; // Obtenemos el nombre de las columnas BD..
            //$cont++;    
            
        // ADDD                
// Obtener PK  // $meta['flags'][1]
            $longFlags = count($meta['flags']); // Obtengo los flags -> PK, not null, multiple_key...
            // Si el flags tiene 2 elementos en este array 
            if($longFlags > 1 && $meta['flags'][1] === 'primary_key'){ // POS 1 = PK | unique_key | multiple_key
                $identificador = $meta['name'];
            } // Unique, FK...
            $campos[][$identificador] = $meta['name']; // Obtenemos el nombre de las columnas BD..
            $cont++;    
        } // FIN ADD

        var_dump($campos);
        return $campos; //SHOW TABLES FROM BD...
    }
    
    public function getTuplas($sql) {
        $campos = [];
        // Si se pierde la conexion, volvemos a conectar...
        if ($this->con == null) {
            $this->con = $this->conexion();
        }
        
        $r = $this->con->prepare($sql); // Preparar una sentencia SQL parametrizada...
        
        /*
        $consulta = $this->con->prepare($sql); // Preparar una sentencia SQL parametrizada...
        $consulta->execute(array(":nom"=>$tienda)); // Ejecuta una sentencia SQL y devuelve el nº de filas afectadas
        while ($fila = $consulta->fetch()){
            echo "Visualizo el producto $fila[0]<br/>";
        }*/
        
        return $datos;
    }
}
