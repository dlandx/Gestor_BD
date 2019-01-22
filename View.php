<?php

class View {
    
    function __construct() {
    }
    
    public function viewBD($datos){
        $check = "";
        foreach ($datos as $value) {
            $check .= "<input type='radio' name='bd_host' value='$value[0]' /> $value[0]<br>";
        }        
        return $check;
    }
    
    public function viewTables($datos) {
        $check = "";
        foreach ($datos as $value) {
            $check .= "<input type='submit' name='tablas_bd' value='$value[0]' >";
        }        
        return $check;
    }
    
    /**
     * Funcion para mostrar el titulo 'nombre de las columnas BD' en la tabla <thead>...
     * @param type $datos (array) Vector con los datos de la tabla BD 'nombre de las columnas'
     * @return string, retorna th de la tabla (Titulo de la tabla)
     */
    public function tableHead($datos): string {
        $info = ""; $pk = null;
        // Obtener solo nombre_corto y PVP...
        foreach ($datos as $value => $key) {
            //$info .= "<th>$value</th>"; //SIN PK flags line 113 BBDD.php
            $pk = key($key);
            $info .= "<th>".$key[$pk]."</th>";
            
//            //echo "$value";
//            foreach ($value as $v) {
//                echo "$v -- ".key($value); // Obtener Key
//            }
        }
        return "<thead><tr>$info<th>Modificar</th><th>Borrar</th><th><input type='text' name='pk' value='$pk'></th></tr></thead>";
    }
    
    /**
     * Funcion que retorna el <tbody> de la tabla 'Datos de cada tupla de la BD'...
     * @param type $datos (array), vector que contiene info de cada tupla de la BBDD...
     * @return string, retorna td 'fila' de la tabla (info de cada tupla BD)
     */
    public function tableBody($datos): string {
        $info = "";
        $long = count($datos); // Longitud del array...
        
        // Recorremos el array de datos = nº de filas de la Tabla...
        for ($i = 0; $i < $long; $i++){
            $info .= "<tr>";
            $tupla = $datos[$i]; // Por cada tupla contendra una serie de registros...
            // Recorremos los registros de cada tupla = datos de las columnas de cada fila...
            foreach ($tupla as $value) {
                $info .= "<td>$value</td>";
            }
            $info .= "<td><input type='submit' value='Editar' name='editar[$value]'></td>"
                    . "<td><input type='submit' value='Eliminar' name='$value'></td></tr>";
        }
        
        return "<tbody>$info</tbody>";
    }
}
