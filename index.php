<?php

//--------------------import--------------------
require_once('./classes/Config.php');
require_once('./classes/phpFlickr.php');
require_once('./classes/Helper.php');

require_once('./classes/Geo.php');
require_once('./classes/Photo.php');
require_once('./classes/Search.php');

require_once('./classes/UI.php');


//--------------------setup--------------------
//config setup
$conf = new Config("./config/config.ini");
//flickr setup
$f = new phpFlickr($conf->p("api_key"));
//cache setup
$f->enableCache("db", $conf->generateDbConnectionString());

//setup a search and UI
$ms = new Search($f);
$ui = new UI(); //to be new UI($f); to bude asi nejlepsi :)))



//--------------------logic--------------------

if(isset ($_REQUEST["search"])) {
    //
    $ms->genericSearch(15); //it will read REQUEST fields and decide what kind of search to do and whether or not to commit :)
}

//UI knows, if there's been a search and if it was sucessful, so it can react properly..
$ui->buildUI($ms);

//print everything out;
print $ui->getPage();



?>
