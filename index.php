<?php

//--------------------import--------------------
require_once('./classes/Config.php');
require_once('./classes/phpFlickr.php');
require_once('./classes/Helper.php');

require_once('./classes/Geo.php');
require_once('./classes/Media.php');
require_once('./classes/Video.php');
require_once('./classes/Photo.php');

require_once('./classes/Search.php');
require_once('./classes/Rerank.php');

require_once('./classes/UI.php');


//--------------------setup--------------------
//config setup
$conf = new Config("./config/config.ini");
//flickr setup
$f = new phpFlickr($conf->p("api_key"));
//cache setup
$f->enableCache("db", $conf->generateDbConnectionString());

//setup a search, Rerank a UI
$ms = new Search($f);
$rr = new Rerank($ms);
$ui = new UI($ms, $rr);



//--------------------logic--------------------
if (isset($_REQUEST["search"])) {
    //search and rerank?
    $ms->genericSearch(); //it will read REQUEST fields and decide what kind of search to do and whether or not to commit :)

    if (isset($_REQUEST["rerank"])) {
        $rr->genericRerank();
    }
}

//UI knows, if there's been a search and if it was sucessful, so it can react properly..
$ui->buildUI();

//print everything out;
print $ui->getPage();


?>
