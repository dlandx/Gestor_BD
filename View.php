<?php

class View {
    
    function __construct() {
    }
    
    // Mostrar las BBDD del Host
    public function viewBD($datos){
        $check = "";
        foreach ($datos as $value) {
            $check .= "<input type='radio' name='bd_host' value='$value[0]' /> $value[0]<br>";
        }        
        return $check;
    }
    
    // Mostrar las TABLAS de la BD
    public function viewTables($datos) {
        $btn = "";
        foreach ($datos as $value) {
            $btn .= "<input type='submit' name='tablas_bd' value='$value[0]' >";
        }        
        return $btn;
    }
    
    /**
     * Funcion para mostrar el titulo 'nombre de las columnas BD' en la tabla <thead>...
     * @param type $datos (array) Vector con los datos de la tabla BD 'nombre de las columnas'
     * @return string, retorna th de la tabla (Titulo de la tabla)
     */
    public function tableHead($datos): string {
        $info = ""; 
        foreach ($datos as $value) {
            $info .= "<th>$value</th>";
        }
        return "<thead><tr>$info<th>Modificar</th><th>Borrar</th></tr></thead>";
    }
    
    /**
     * Funcion que retorna el <tbody> de la tabla 'Datos de cada tupla de la BD'...
     * @param type $datos (array), vector que contiene info de cada tupla de la BBDD...
     * @return string, retorna td 'fila' de la tabla (info de cada tupla BD)
     */
    public function tableBody($thead, $datos): string {
        $resultado = "";
        
        foreach ($datos as $pos => $tupla) {
            $resultado .= "<tr><form action='gestionarTabla.php' method='POST'>";
            foreach ($tupla as $key => $value) {
                $resultado .= "<td>$value</td>"
                        . "<input type='hidden' value=\"".(addslashes($value))."\" name='celda[{$thead[$key]}]'>";
            }
            $resultado .= "<td><input type='submit' value='Editar' name='btn'></td>"
                . "<td><input type='submit' value='Eliminar' name='btn'></td></form></tr>";
        }
        return "<tbody>$resultado</tbody>";
    }
    
    // Crear el formulario con los campos correspondientes...
    public function editTableForm($column, $identify, $datos) {
        $info = "";
        foreach ($column as $pos => $name) {
            $editable  = ""; // Indicar que campo o INPUT se puede editar...
            // Obtener nombre columnas de los identificadores [PK, FK...]
            $ident = array_unique($identify[$pos]); // Si se repite que lo quite [Multi-Key una PK puede repetir]
            $info .= "<label>$name</label>"; // Nombre de las columnas TABLA BD...
            foreach ($ident as $key => $value) { // Columna es Identificadores INPUT no editable...
                $editable = "readonly";
            }
            // Añadimos los INPUT o campo de texto de la TUPLA...
            $info .= "<input type='text' $editable placeholder='Ingresar $name' name='new[:$name]' value='$datos[$name]'><br>";
        }
        
        return $info;
    }
    
    
    
    
    // Obtener la posición de la columna que sea PK de la TABLA BD
    private function getPrimaryKey($identificador) {
        $pk_pos = []; // $identificador -> array [0] = ['tipo identificador' => nombre columna];
        // Recorremos el array...
        foreach ($identificador as $pos => $datos) {
            // Por cada posición recorremos los datos = identificadores...
            foreach ($datos as $key => $value) {
                // Si el valor es de tipo PK almacenamos en un array (Por si hay más de una PK)...
                if ($key === 'primary_key') {
                    $pk_pos[] = $pos; // Añadimos la posición de la PK de la TABLA... 
                }
            } // For identificadores...
        }
        return $pk_pos;
    }
    
    // Obtener los datos de la celda donde la columna sea PK...
    private function getDataPK($posPK, $posColumn, $datoCelda) { // NO IMPLEMENT ERROR... LINE 58
        $info = "";
        // Recorremos las PK que tenga la TABLA...
        foreach ($posPK as $value) {
            // Cuando coincida el nº de registro (columna) con la posición (columna) de la PK 
            if ($value == $posColumn) { // Si la columna coincide con la columna PK
                // Obtenemos el valor del registro en dicha posición = Columna PK obtener valor de la celda... 
                // Antes comprobamos que SI tiene más de una PK (Array) SINO solo el dato (string)...
                //(count($posPK) > 1) ? $infoPK[$value] = $datoCelda : $infoPK = $datoCelda;

                if (count($posPK) > 1){
                    $info[$value] = $datoCelda;
                } else { // La TABLA solo tiene un PK
                    $info = $datoCelda;
                }
            }
        }        
        return $info;
    }
    
}