<?php

/**
 * Clase 'BBDD.php' -> Presentación de datos del modelo (HTML)
 * IMPORTANTE -> Ctrl+Shift+Plus [Ctrl + Shift + '+'] (Para despledar todos las secciones de esta clase)
 */

class View {
    
    // <editor-fold defaultstate="collapsed" desc="Constructor">
    /**
     * Constructor de la clase...
     */
    function __construct() {
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Métodos públicos">
    /**
     * Mostrar en HTML las BBDD que tiene el HOST ingresado.
     * @param array $datos, array con los nombres de las BBDD...
     * @param string $bd, nombre de la BBDD que se selecciono anteriormente para mantener el CHECK...
     * @return string, RadioGroup con los nombres de las BBDD que tenga el HOST elegido... 
     */
    public function viewBD(array $datos, string $bd): string{
        $check = "";
        $checked = ""; // Mostrar el CHECK seleccionado...
        foreach ($datos as $value) {
            if ($value[0] == $bd){ // La BBDD que se selecciono anteriormente queda CHECK
                $checked = "checked";
            }
            
            $check .= "<div class='check'><label class='labelCheck'>$value[0]</label>"
                    . "<input type='radio' $checked name='bd_host' value='$value[0]' /></div>";                            
            //$check .= "<input type='radio' $checked name='bd_host' value='$value[0]' /> $value[0]<br>";
            $checked = "";
        }        
        return $check;
    }

    /**
     * Mostrar las TABLAS de la BBDD seleccionada en BTN...
     * @param array $datos, nombres de las TABLAS que tanga la BBDD seleccionada...
     * @return string, mostrar en BTN los nombres de las TABLAS que tenga la BBDD...
     */
    public function viewTables(array $datos): string {
        $btn = "";
        foreach ($datos as $value) {
            $btn .= "<input type='submit' class='bd' name='tablas_bd' value='$value[0]' >";
        }        
        return $btn;
    }
    
    /**
     * Funcion para mostrar el titulo 'nombre de las columnas BD' en la tabla <thead>...
     * @param array $datos, Vector con los datos de la tabla BD 'nombre de las columnas'
     * @return string, retorna th de la tabla (Titulo de la tabla)
     */
    public function tableHead(array $datos): string {
        $info = ""; 
        foreach ($datos as $value) {
            $info .= "<th>$value</th>";
        }
        return "<thead><tr>$info<th>Modificar</th><th>Borrar</th></tr></thead>";
    }
    
    /**
     * Funcion que retorna el <tbody> de la tabla 'Datos de cada tupla de la BBDD'...
     * @param array $thead, nombre de las columnas de la TABLA de la BBDD...
     * @param array $datos (array), vector que contiene info de cada tupla de la BBDD...
     * @return string, retorna tr 'fila' de la tabla (info de cada tupla BD)
     */
    public function tableBody(array $thead, array $datos): string {
        $resultado = "";
        
        foreach ($datos as $pos => $tupla) { // Recorremos para cada fila, crearemos 'x' celdas...
            $resultado .= "<tr><form action='gestionarTabla.php' method='POST'>";
            foreach ($tupla as $key => $value) {// CONTROLAR SCAPE EN INTUP *
                $resultado .= "<td>$value</td>"
                        . "<input type='hidden' value=\"".(addslashes($value))."\" name='celda[:{$thead[$key]}]'>"; // : error editar
            }
            $resultado .= "<td><input type='submit' value='Editar' name='btn' class='edit'></td>"
                . "<td><input type='submit' value='Eliminar' name='btn' class='delete'></td></form></tr>";
        }
        return "<tbody>$resultado</tbody>";
    }
    
    /**
     * FORMULARIO EDITAR -> crear formulario con los campos correspondientes...
     * @param array $column, Nombre de las columnas
     * @param array $identify, Identificadores de la TABLA, nombre columnas que son PF, FK, Multi-Key...
     * @param array $datos, datos que tendra el INPUT para poder editar si lo desea...
     * @return string, crear un FORMULARIO con los (LABEL e INPUT) correspondientes...
     */
    public function editTableForm(array $column, array $identify, array $datos): string {
        $info = "";/*
        foreach ($column as $pos => $name) { // Nombre de las columnas...
            $editable  = ""; // Indicar que campo o INPUT se puede editar...
            // Obtener nombre columnas de los identificadores [PK, FK...]
            $ident = array_unique($identify[$pos]); // Si se repite que lo quite [Multi-Key una PK puede repetir]
            $info .= "<label>$name</label>"; // Nombre de las columnas TABLA BD...
            foreach ($ident as $key => $value) { // Columna es Identificadores INPUT no editable...
                $editable = "readonly";
            }
            // Añadimos los INPUT o campo de texto de la TUPLA...
            $name = ":$name";
            $info .= "<input type='text' $editable placeholder='Ingresar $name' name='new[$name]' value='$datos[$name]'><br>";
        }*/
        
        foreach ($column as $pos => $name) { // Nombre de las columnas...
            $editable  = ""; // Indicar que campo o INPUT se puede editar...
            // Obtener nombre columnas de los identificadores [PK, FK...]
            $ident = array_unique($identify[$pos]); // Si se repite que lo quite [Multi-Key una PK puede repetir]
            foreach ($ident as $key => $value) { // Columna es Identificadores INPUT no editable...
                $editable = "readonly class='key'";
            }
            // Añadimos los INPUT o campo de texto de la TUPLA...
            $nameKey = ":$name";
            $info .= "<div class='filds'>"
                    . "<input type='text' $editable placeholder='Ingresar $name' name='new[$nameKey]' value='$datos[$nameKey]'>"
                    . "<label>$name</label></div>";
        }
        return $info;
    }
    
    /**
     * FORMULARIO INSERTAR -> crear formulario con los campos correspondientes...
     * @param array $column, Nombre de las columnas
     * @param array $identify, Identificadores de la TABLA, nombre columnas que son PF, FK, Multi-Key...
     * @param string $datos, datos que tendre el INPUT para poder editar si lo desea...
     * @return string, crear un FORMULARIO con los (LABEL e INPUT) correspondientes...
     */
    public function insertTableForm(array $column, array $identify, $datos): string {
        $info = "";
        foreach ($column as $pos => $name) { // Nombre de las columnas...
            $id  = ""; // Indicar que campo o INPUT es un identificador [PK, FK...]
            // Obtener nombre columnas de los identificadores [PK, FK...]
            $ident = array_unique($identify[$pos]); // Si se repite que lo quite [Multi-Key una PK puede repetir]
            foreach ($ident as $key => $value) { // Columna es Identificadores INPUT editable...
                $id = "class='key'"; // Diferenciar de los demas para que sepa que es un identificador...
            }
            // Añadimos los INPUT o campo de texto de la TUPLA...
            $nameKey = ":$name";
            $info .= "<div class='filds'>"
                    . "<input type='text' $id placeholder='Ingresar $name' name='new[$nameKey]' value='$datos[$nameKey]'>"
                    . "<label>$name</label></div>";
        }
        
        return $info;
    }
    // </editor-fold>
    
}