<?php
    

require_once('../classes/Rerank.php');

function createJsArray($array = array(), $name = 'custom_array'){
    $out = "var " . $name . " = new Array();" . "\n";

    foreach ($array as $key => $value) {
        $out .= $name ."[" . $key . "] = \"" . $value . "\";" . "\n";  // e.g. myCars[0]="Saab";
    }

    return $out;
}

?>

//javascript content

<?php print createJsArray(Rerank::$types, "rerankTypes"); ?>

