<?php

class View {
    
    function __construct() {
    }
    
    // Mostrar la BD del Host
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
    public function tableBody($datos, $identify): string {
        $resultado = "";
        $info_PK = null;
        
        // Funcion donde recorremos el identificador y obtenemos la posición de la PK...
        $posPK = $this->getPrimaryKey($identify); // Obtenemos la POSICION donde este la PK de la TABLA
        // Recorremos el array de datos = nº de filas de la Tabla...
        foreach ($datos as $pos => $tupla) {
            $resultado .= "<tr>";
            // Por cada posición contendra una serie de registros = tupla (datos)...
            // Recorremos los registros de cada tupla = datos de las columnas de cada fila...
            foreach ($tupla as $key => $value) {
                $resultado .= "<td>$value</td>";
                // Funcion - Obtener el dato de la columa que es PK...
                //$this->getDataPK($posPK); // Posición columna PK, 
                //echo "$key - $value<br>";
                
                // Recorremos las PK que tenga la TABLA...
                foreach ($posPK as $v) {
                    // Cuando coincida el nº de registro (columna) con la posición (columna) de la PK 
                    if ($v == $key) {
                        // Obtenemos el valor del registro en dicha posición = Columna PK obtener valor de la celda... 
                        // Antes si tiene más de una PK la TABLA concatenamos los valores...
                        if (count($posPK) > 1){
                            // CONTROLAR CONCATENACION....
                            $info_PK[] = $value; // Delimitador sera (:-:)
                        } else { // La TABLA solo tiene un PK
                            $info_PK = $value; // Obtener valor
                        }                        
                    }
                    //echo "<h2>$v - $key -> $value</h2>";
                }
            }
            


            $resultado .= "<td><input type='submit' value='Editar' name='editar[$info_PK]'></td>"
                . "<td><input type='submit' value='Eliminar' name='$info_PK'></td></tr>";
        }
 
        return "<tbody>$resultado</tbody>";
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
    private function getDataPK($posPK) {
        foreach ($posPK as $value) {
            
        }
    }
    
    public function body($datos, $pk) {
        $info = ""; $info_pk = null;
        $long = count($datos); // Longitud del array...
        
        // Obtener la columana donde tenga PK la BD...
        $pk_bd = [];
        for ($i = 0; $i < count($pk); $i++){
            $tupla = $pk[$i];
            foreach ($tupla as $value => $key) {
                if ($value === 'primary_key'){
                    $pk_bd []= $i; // Obtenemos la pos
                }
            }
        }
        var_dump($pk_bd);
   /*     $ref_pk = "";
        if (!empty($pk_bd)){ // Si tiene PK 
            if (count($pk_bd) > 1) { // Tiene mas de 1 PK
                
            } else {
                $ref_pk = $pk_bd[0];
            }
        }
        echo $ref_pk;
 */      
        
        // Recorremos el array de datos = nº de filas de la Tabla...
        for ($i = 0; $i < $long; $i++){
            $info .= "<tr>";
            $tupla = $datos[$i]; // Por cada tupla contendra una serie de registros...
            // Recorremos los registros de cada tupla = datos de las columnas de cada fila...
            for ($j = 0; $j < count($tupla); $j++) {
                $info .= "<td>$tupla[$j]</td>";

                foreach ($pk_bd as $value) { // De la columan que es PK obtenemos el dato...
                    if ($j == $value) { // Si la tupla coincide con la columna PK
                        //$info_pk = $tupla[$j]; // Bien si solo tiene 1 PK
                        
                        // Validar que en caso de que tenga 2 concat...
                        if (count($pk_bd) > 1){
                            //echo "<i>MAS</i>";
                            $info_pk .= "$tupla[$j]:-:";// Delimitador
                        } else { // 1 PK
                            $info_pk = $tupla[$j];
                        }
                        //echo "$j - $tupla[$j]<br>";
                    }
                    
                }
            }
            $info .= "<td><input type='submit' value='Editar' name='editar[$info_pk]'></td>"
                    . "<td><input type='submit' value='Eliminar' name='$info_pk'></td></tr>";
            
            
        }
        
        //var_dump($pk);
        echo "DATOS";
        //var_dump($datos);
        
        return "<tbody>$info</tbody>";
    }
    
    private function functionName($param) {
        
    }
}
