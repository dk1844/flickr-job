<?php
function createBoth($name){
    $capt = ucfirst($name);

$template = '
    public function get%s() {
        return $this->%s;
    }

        public function set%s($%s) {
        $this->%s = $%s;
    }

';

return sprintf($template,$capt,$name,$capt,$name,$name, $name);

}


if (!isset ($_POST["source"]) ){
    $dest = '';
    $source = '';
} else {
     $source = $_POST["source"];

     $lines = explode(";", $source);

     $out = "";

     foreach ($lines as $key => $line) {
         $line = str_replace("private", "", $line);
         $line = str_replace("$", "", $line);
         $line = trim($line); //only leave the variable name

         if (empty($line)) { //strip empty lines
             unset ($lines[$key]);
         } else {
            $lines[$key] = $line;
         }
     }


     foreach ($lines as $key => $line) {
        $out .= createBoth($line); //create setter & getter

     }


     //debug
        print "<pre>";
       print_r($lines);
        print "</pre>";
     
$dest = $out;


}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>PHP Encapsulation Helper</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  </head>
  <body>
      accepts lines with:<br/>
      private $nameOfVar;<br/>
      private $nameOfOther_Var;<br/>
      ...<br/>
      <form method="post" action="<?php print $_SERVER["PHP_SELF"] ?>">
          <textarea cols="60" rows="15"  name="source"><?php
            print $source;
          ?></textarea>
          <input type="submit" value="Go" />
      </form>
      <textarea cols="60" rows="45"  name="destination"><?php
        print $dest;
      ?></textarea>
  </body>
</html>
