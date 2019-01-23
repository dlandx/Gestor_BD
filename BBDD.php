<?php

class BBDD {
    
    private $con;
    private $error;
    private $host;
    private $user ;
    private $pass;
    private $bd;
    private $identify; // Array donde obtendremos los PK, FK... de la TABLA.
    
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
    

    //Función obtiene los nombres de las columnas de la BBDD...    
    public function nameColumnTable($tabla) {
        $campos = []; // Datos que contendra el nº de las columnas de la Tabla...
        $cont = 0; // Contador para el While(...) -> Recorrer columnas...
        $identify = []; // Identificadores de la Tabla -> PK, FK...
                
        if ($this->con == null) { // Si se pierde la conexion, volvemos a conectar...
            $this->con = $this->conexion();
        }
                
        $r = $this->con->query("SELECT * FROM $tabla"); // Preparar la consulta SQL...
        // columnCount() -> Devuelve el número de columnas de un conjunto de resultados.
        $columns = $r->columnCount(); // Nº de columnas que tendra la TABLA de la BBDD...

        // Bucle - obtendremos los nombres de las columnas de la TABLA BD...
        while ($cont < $columns) {
            // Array de objetos de cada columna [tipo=VAR_STRING, flags=(not null, PK), long, tabla...]
            $meta = $r->getColumnMeta($cont); //Devuelve metadatos de una columna de un conjunto de resultados            
            $campos[] = $meta['name']; // Obtenemos del METADATO el nombre de la columnas TABLA...
            
            // $this->identificadoresTabla($meta) -> obtener de los METADATOS solo los flags [PK, FK, Multi-PK]
            // Obtener identificadores de la Tabla y nombre de la columna (array asociativo)...
            $identify[] = $this->identificadoresTabla($meta); 
            $cont++;      
        }
        
        $this->identify = $identify; // Asignamos al atributo el resultado = los idenficadores obtenidos.
        return $campos; // Retornamos el nombre de las columnas de la TABLA... 
    }
    
    // Obtener identificadores de la Tabla colo la Primary Key, Unique Key, Multiple Key y que columna esta...
    private function identificadoresTabla($meta) {
        $identificador = [];
        // flags -> Bandera establecida para esta columna [En ella estan PK, Not NULL, FK, blob...]
        foreach ($meta['flags'] as $value) { // Recorrer solo los flags del METADATO...
            // Si el flags (array de banderas) -> Contiene los tipos PK, FK o Multi-Key
            if ($value === 'primary_key' || $value === 'unique_key' || $value === 'multiple_key'){
                $identificador[$value] = $meta['name']; // Guardamos array[tipo flags] = Nombre columna; 
            }
        }
        return $identificador;
    }
    
    // Retornar los identificadores que tenga la TABLA como la PK, FK, Multi-Key...
    public function getIdentifyTable() {
        return $this->identify;
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
