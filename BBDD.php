<?php

class BBDD {
    
    private $con;
    private $error;
    private $host;
    private $user ;
    private $pass;
    private $bd;
    private $identify; // Array donde obtendremos los PK, FK... de la TABLA.
    
    function __construct(array $conexion) {
        // Obtenemos los datos de conexion [host, $user, $pass, $bd]
        foreach ($conexion as $tipo => $datos) {
            switch ($tipo) {
                case 'host':
                    $this->host = $datos;
                    break;

                case 'user':
                    $this->user = $datos;
                    break;
                
                case 'pass':
                    $this->pass = $datos;
                    break;
                
                case 'bd':
                    $this->bd = $datos;
                    break;
                
                default:
                    break;
            }
//            ($tipo === 'host')? $this->host = $datos : break;
//            ($tipo === 'user')? $this->user = $datos : $this->user = null;
//            ($tipo === 'pass')? $this->pass = $datos : $this->pass = null;
//            ($tipo === 'bd')? $this->bd = $datos : $this->bd = null;
        }
        
        $this->con = $this->conexion(); // Establecemos la conexión con la BBDD...
        $this->error = ($this->con) ? true : false; // Informacion de conexión... (OK | Error)
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
        } catch (PDOException $ex) {
            $this->error = $ex->getMessage(); // Información de el Error...
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

    // Obtener los datos del la BBDD [Tablas, Tuplas]
    public function getDatosBD($sql){
        $bd = [];
        // Si se pierde la conexion, volvemos a conectar...
        if ($this->con == null) {
            $this->con = $this->conexion();
        }
        
        // Preparar sentencia...
        $result = $this->con->query($sql);
        // fetch() Obtiene la siguiente fila de un conjunto de resultados...
        while ($datos = $result->fetch(PDO::FETCH_NUM)) {
            $bd[] = $datos; // Almacenar los Datos obtenidos BBDD...
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
    
    // Obtener identificadores de la Tabla solo la Primary Key, Unique Key, Multiple Key y que columna esta...
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
    
    // Realizar parte del CRUD -> UPDATE una fila modificada mediante FORM...
    public function update($tabla, $campos, $identify, $datos) {
        $cont = 0;
        $set = "SET ";
        $where = "WHERE ";
        // Si se pierde la conexion, volvemos a conectar...
        if ($this->con == null) {
            $this->con = $this->conexion();
        }
        
        // Identificadores que tenga la TABLA [PK, FK, Multi-KEY] -> para la CONDICION = WHERE (...)
        foreach ($identify as $pos => $column) { 
            $column = array_unique($column); // No repetir valores [PK y Multi-KEY en la misma columna]
            foreach ($column as $name) { // Obtenemos el nombre del identificador
                $where .= " $name=:$name AND "; // Obtener key...
            }            
        }        
        // Campos a actualizar sentencia SET (...)
        foreach ($datos as $key => $value) {
            $set .= "$campos[$cont]=$key, ";            
            $cont++;
        }
        
        // Eliminamos los CARACTERES sobrantes (, ) para realizar la SENTENCIA...
        $sql = "UPDATE $tabla ".substr($set, 0, strlen($set) - 2)." ".substr($where, 0, strlen($where) - 4);
        $stmt = $this->con->prepare($sql); // Preparar una sentencia SQL parametrizada...
        return $stmt->execute($datos); // Ejecuta una sentencia preparada (Retorna True | False)
    }
    
    // Realizar parte del CRUD -> DELETE una fila modificada mediante FORM...
    public function delete($tabla, $datos) {
        // Si se pierde la conexion, volvemos a conectar...
        if ($this->con == null) {
            $this->con = $this->conexion();
        }

        // Crear sentencia parametrizada...
        $sql = "DELETE FROM $tabla WHERE ";
        foreach ($datos as $campo => $dato) {
            $columna = substr($campo,1); // Quitar : = KEY del array asociativo...
            $sql .= "$columna=$campo AND ";
        }
        
        //quitamos el último and, para que la sentencia quede correcta
        $sql = substr($sql, 0, strlen($sql) - 4);
        $stmt = $this->con->prepare($sql); // Preparar una sentencia SQL parametrizada...
        return $stmt->execute($datos); // Ejecuta una sentencia preparada (Retorna True | False)
    }
    
    public function insert($tabla, $datos) {
        $column = "(";
        $valor = "VALUES (";
        
        // Si se pierde la conexion, volvemos a conectar...
        if ($this->con == null) {
            $this->con = $this->conexion();
        }

        // Crear sentencia parametrizada...
        foreach ($datos as $campo => $dato) {
            $columna = substr($campo,1); // Quitar : = KEY del array asociativo...
            $column .= "$columna, "; //"$columna=$campo AND ";
            $valor .= "$campo, ";
        }
        
        // Sentencia SQL -> quitando las ultimas comas [Nombre columnas y VALUES...]
        $sql = "INSERT INTO $tabla ".substr($column, 0, strlen($column) - 2).") ".substr($valor, 0, strlen($valor) - 2).");";
        $stmt = $this->con->prepare($sql); // Preparar una sentencia SQL parametrizada...
        return $stmt->execute($datos); // Ejecuta una sentencia preparada (Retorna True | False)
        // Mostrat un mensaje en caso de error...
    }
    
    
    
    
    
    public function eliminar($tabla, $identify, $datos){
        // Si se pierde la conexion, volvemos a conectar...
        if ($this->con == null) {
            $this->con = $this->conexion();
        }

        // Crear sentencia parametrizada...
        $sql = "DELETE FROM $tabla WHERE ";
        foreach ($identify as $pos => $column) { // Identificadores que tenga la TABLA [PK, FK, Multi-KEY]
            $column = array_unique($column); // No repetir valores [PK y Multi-KEY en la misma columna]
            foreach ($column as $key => $columna) { // Obtenemos el nombre del identificador
                $sql .= "$columna='$datos[$pos]' AND ";
            }
        }
        //quitamos el último and, para que la sentencia quede correcta
        $sql = substr($sql, 0, strlen($sql) - 4);

        $r = $this->con->prepare($sql); // Preparar una sentencia SQL parametrizada...
        $r->execute($datos); // NO PARAMETRIZADO
        
        $res = $r->rowCount();//Devuelve el número de filas afectadas por la última sentencia SQL
        var_dump($res);
    }
    
    public function getRowEdit($tabla, $identify, $datos) {
        $tupla = [];
        // Si se pierde la conexion, volvemos a conectar...
        if ($this->con == null) {
            $this->con = $this->conexion();
        }
        
        $sql = "SELECT * FROM $tabla WHERE ";
        foreach ($identify as $pos => $column) { // Identificadores que tenga la TABLA [PK, FK, Multi-KEY]
            $column = array_unique($column); // No repetir valores [PK y Multi-KEY en la misma columna]
            foreach ($column as $key => $columna) { // Obtenemos el nombre del identificador
                $sql .= "$columna='$datos[$pos]' AND ";
            }
        }
        //quitamos el último and, para que la sentencia quede correcta
        $sql = substr($sql, 0, strlen($sql) - 4);
        
        $r = $this->con->prepare($sql); // Preparar una sentencia SQL parametrizada...
        $r->execute($datos); // NO PARAMETRIZADO
        while ($fila = $r->fetch(PDO::FETCH_NUM)){
            $tupla = $fila;
        }
        return $tupla;
    }
    
    
}
