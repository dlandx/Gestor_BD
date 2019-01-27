<?php

/**
 * Clase 'BBDD.php' -> Conexión con la Base de Datos (PDO)
 * IMPORTANTE -> Ctrl+Shift+Plus [Ctrl + Shift + '+'] (Para despledar todos las secciones de esta clase)
 */

class BBDD {
    
    // <editor-fold defaultstate="collapsed" desc="Atributos">
    private $con;
    private $error;
    private $host;
    private $user ;
    private $pass;
    private $bd;
    private $identify; // Array donde obtendremos los PK, FK... de la TABLA.
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Constructor">
    /**
     * Constructor de la clase, instanciar los atributos...
     * @param array $conexion, SESSION[] que contiene los datos para la conexión
     */
    function __construct(array $conexion) {
        // Obtenemos los datos de conexion SESSION[host, $user, $pass, $bd]
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
        }
        
        $this->con = $this->conexion(); // Establecemos la conexión con la BBDD...
        $this->error = ($this->con) ? true : false; // Informacion de conexión... (OK | Error)
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Métodos privados">
    /**
     * Establecemos la conexión con la BBDD, en caso de NO conexión informar del error...
     * @return \PDO, conexión a una base de datos... (resultado al crea una instancia de PDO)
     */
    private function conexion(): PDO {     
        try {
            $dsn = "mysql:host=$this->host; dbname=$this->bd";
            $atributos = [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", 
                PDO::ATTR_ERRMODE => true, // True o false da igual si esta BIEN... 
                PDO::ERRMODE_EXCEPTION => true]; // Errores para Excepciones (Solo para desarrollo)...
            // Para establecer una conexión lo que hacemos es instanciar un objeto de la clase PDO()...
            $con = new PDO($dsn, $this->user, $this->pass, $atributos);
        } catch (PDOException $ex) {
            $this->error = $ex->getMessage(); // Información de el Error...
            die ("Se produjo un error en la conexion: ".$ex->getMessage());
        } // try

        return $con; // Retorna la conexión que instanciamos en el objeto de la clase PDO(...)
    }
    
    /**
     * Obtener identificadores de la Tabla solo la Primary Key, Unique Key, Multiple Key y que columna esta...
     * @param type $meta (Array) metadatos de una columna de un conjunto de resultados...
     * @return array, Retornar de los METADATOS SOLO los flags [PK, FK, Multi-PK]...
     */
    private function identificadoresTabla(array $meta): array {
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
    
    /**
     * Preparar una sentencia SQL parametrizada para reutilizar para la parte del CRUD...
     * @param string $sql, Sentencia SQL a realizar...
     * @param array $datos, Array asociativo contendra los datos a insertar ($stmt->execute(array)...)
     * @return bool, Resultado al ejecutar la sentencia TRUE=OK | FALSE=ERROR...
     */
    private function prepareStmt(string $sql, array $datos): bool {
        $stmt = $this->con->prepare($sql); // Preparar una sentencia SQL parametrizada...
        $r = $stmt->execute($datos); // Ejecuta una sentencia preparada (Retorna True | False)
        // errorInfo() -> Obtiene información ampliada del error asociado con la última operación...
        $error = $stmt->errorInfo(); // Posisión 2 -> Mensaje de error específico del controlador.
        $this->error = ($error[2] === null)? "Datos insertado correctamente en la tabla ($tabla)." 
                : "<h3>Error insertar, tener encuenta las relaciones de integridad referencial</h3>"
                    . "<p>$error[2]</p><hr><h3>Sentencia SQL ejecutada</h3><p>{$stmt->queryString}</p>";
        $stmt->closeCursor(); // Cerramos el cursor
        return $r; // (Retorna True | False) del resultado -> $stmt->execute(...);
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="CRUD - Métodos públicos">
    /**
     * CRUD -> SELECT obtener los datos del la BBDD [Tablas, Tuplas]...
     * @param string $sql, Sentencia SQL a realizar...
     * @return array, Tuplas obtenidas de la BBDD... 
     */
    public function getDatosBD(string $sql): array{
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
        $result->closeCursor(); // Cerrar cursor...
        return $bd;
    }
    
    /**
     * CRUD -> UPDATE actualizar los datos de una fila modificada mediante FORM...
     * @param string $tabla, Nombre de la TABLA de la BBDD donde se actualizaran los datos... 
     * @param array $campos, Campos a actualizar en la BBDD (Nombre columnas)...
     * @param array $identify, Identificadores de la TABLA BBDD, nombre columnas que son (PK, FK, Multi-KEY...)
     * @param array $datos, Datos a actualizar en la BBDD...
     * @return bool, Resultado al ejecutar una sentencia preparada (true=SI realizo la operación, false=NO)...
     */
    public function update(string $tabla, array $campos, array $identify, array $datos): bool {
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
        // Realizar la sentencia parametrizada y retornar el estado de la operación (TRUE=OK | FALSE=ERROR)...
        return $this->prepareStmt($sql, $datos, $LIKE);

        /*
        // Eliminamos los CARACTERES sobrantes (, ) para realizar la SENTENCIA...
        $sql = "UPDATE $tabla ".substr($set, 0, strlen($set) - 2)." ".substr($where, 0, strlen($where) - 4);
        $stmt = $this->con->prepare($sql); // Preparar una sentencia SQL parametrizada...
        $r = $stmt->execute($datos); // Ejecuta una sentencia preparada (Retorna True | False)
        // errorInfo() -> Obtiene información ampliada del error asociado con la última operación...
        $error = $stmt->errorInfo(); // Posisión 2 -> Mensaje de error específico del controlador.
        $this->error = ($error[2] === null)? "Datos insertado correctamente en la tabla ($tabla)." 
                : "<h2>Error insertar, tener encuenta las relaciones de integridad referencial</h2><p>$error[2]</p><hr><h3>Sentencia SQL ejecutada</h3> {$stmt->queryString}";
        $stmt->closeCursor(); // Cerramos el cursor
        return $r; // (Retorna True | False) del resultado -> $stmt->execute(...);*/
    }
    
    /**
     * CRUD -> DELETE elimar los datos de una fila seleccionada...
     * @param string $tabla, Nombre de la TABLA de la BBDD donde se eliminara los datos... 
     * @param array $datos, Datos a eliminar en la BBDD...
     * @return bool, Resultado al ejecutar una sentencia preparada (true=SI realizo la operación, false=NO)...
     */
    public function delete(string $tabla, array $datos): bool {
        // Si se pierde la conexion, volvemos a conectar...
        if ($this->con == null) {
            $this->con = $this->conexion();
        }

        // Crear sentencia parametrizada...
        $sentencia = "DELETE FROM $tabla WHERE ";
        foreach ($datos as $campo => $dato) {
            $columna = substr($campo,1); // Quitar : = KEY del array asociativo...
            $sentencia .= "$columna=$campo AND ";
        }
        
        //quitamos el último and, para que la sentencia quede correcta
        $sql = substr($sentencia, 0, strlen($sentencia) - 4);    
        // Realizar la sentencia parametrizada y retornar el estado de la operación (TRUE=OK | FALSE=ERROR)...
        return $this->prepareStmt($sql, $datos); 
//        $sql = substr($sql, 0, strlen($sql) - 4);
//        $stmt = $this->con->prepare($sql); // Preparar una sentencia SQL parametrizada...
//        return $stmt->execute($datos); // Ejecuta una sentencia preparada (Retorna True | False)
    }
    
    /**
     * CRUD -> INSERT insertar los datos en la TABLA seleccionada...
     * @param string $tabla, Nombre de la TABLA de la BBDD donde se eliminara los datos...
     * @param array $datos, Datos a insertar en la BBDD...
     * @return bool, Resultado al ejecutar una sentencia preparada (true=SI realizo la operación, false=NO)...
     */
    public function insert(string $tabla, array $datos): bool {
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
        $campos = substr($column, 0, strlen($column) - 2).")";
        $valores = substr($valor, 0, strlen($valor) - 2).");";
        
        $sql = "INSERT INTO $tabla $campos $valores";
        // Realizar la sentencia parametrizada y retornar el estado de la operación (TRUE=OK | FALSE=ERROR)...
        return $this->prepareStmt($sql, $datos);
        /*
        // Sentencia SQL -> quitando las ultimas comas [Nombre columnas y VALUES...]
        $sql = "INSERT INTO $tabla ".substr($column, 0, strlen($column) - 2).") ".substr($valor, 0, strlen($valor) - 2).");";
        $stmt = $this->con->prepare($sql); // Preparar una sentencia SQL parametrizada...
        $r = $stmt->execute($datos); // Ejecuta una sentencia preparada (Retorna True | False)
        // errorInfo() -> Obtiene información ampliada del error asociado con la última operación...
        $error = $stmt->errorInfo(); // Posisión 2 -> Mensaje de error específico del controlador.
        $this->error = ($error[2] === null)? "Datos insertado correctamente en la tabla ($tabla)." 
                : "<h2>Error insertar, tener encuenta las relaciones de integridad referencial</h2><p>$error[2]</p><hr><h3>Sentencia SQL ejecutada</h3> {$stmt->queryString}";
        $stmt->closeCursor(); // Cerramos el cursor
        return $r; // (Retorna True | False) del resultado -> $stmt->execute(...);*/
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Métodos públicos">
    /**
     * Función cierra la conexión con la Base de Datos... 
     */
    public function close() {
        $this->con = null;
    }

    /**
     * Informar si se produce un ERROR o si una operación fue realizada con EXITO...
     * @return (Bool - String), Mensaje de una operación realizada... 
     */
    public function getInfo() {
        return $this->error;
    }
          
    /**
     * Función obtiene los nombres de las columnas de la BBDD... 
     * @param string $tabla, nombre de la TABLA de la BBDD donde se obtendran los datos... 
     * @return array, nombre de las columnas de la TABLA seleccionada de la BBDD...
     */
    public function nameColumnTable(string $tabla): array {
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
        $r->closeCursor(); // Cerrar cursor...
        $this->identify = $identify; // Asignamos al atributo el resultado = los idenficadores obtenidos.
        return $campos; // Retornamos el nombre de las columnas de la TABLA... 
    }
    
    /**
     * Función que almacena los identificadores que tenga la TABLA como la (PK, FK, Multi-Key...)
     * @return array, datos de los identificadores [nombre de la columna y el tipo de identificador]
     */
    public function getIdentifyTable(): array {
        return $this->identify;
    }
    // </editor-fold>
    
}