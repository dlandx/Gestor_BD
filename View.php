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
}
