<?php
    

require_once('../classes/Rerank.php');
require_once('../classes/Search.php');

function createJsArray($array = array(), $name = 'custom_array'){
    $out = "var " . $name . " = new Array();" . "\n";

    foreach ($array as $key => $value) {
        $out .= $name ."[" . $key . "] = \"" . $value . "\";" . "\n";  // e.g. myCars[0]="Saab";
    }

    return $out;
}

?>

//javascript content by php. cool, huh?
//the reason for this si to be able to use it easily in jQuery, without having to change javascript variables a array contents separately.
//This way php takes cares of everything..

//rerank types generated by php.
<?php print createJsArray(Rerank::$types, "rerankTypes"); ?>

//search date types
<?php print createJsArray(Search::$input_date_types, "input_date_types"); ?>

