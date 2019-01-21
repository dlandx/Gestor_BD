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
        echo $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
            //$this->error = "Se produjo un error en la conexion: ".$ex->getMessage();
        } // try
        
        return $con;
    }
    
    /**
     * Función cierra la conexión con la Base de Datos... 
     */
    public function close() {
        $this->con = null;
    }
    
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
        
        var_dump($bd);
            
    }
}
