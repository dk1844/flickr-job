<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

//require_once ("./phpFlickr.php");

/**
 * Description of Search
 *
 * @author Daniel
 */
class Search {
    const DEFAULT_COUNT = 20;
    //const EXTRAS = 'description, license, date_upload, date_taken,
    //    owner_name, last_update, media, geo, tags, views, url_t';

    const EXTRAS = "description, license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_m, url_o";
    // original_format,
    //available search types, 0 is default
    //media rozlisovat?
    public static $types = array(
        "text",
        "latest",
    ); 
    public static $counts = array(
        1, 2, 5,
        10, 20, 50,
        75, 100,
    );
    private $type;
    private $result; //array thingy
    private $f; // flickr object
    private $resultMedias = array(); //array of medias
    private $message;
    private $committed;
    private $searchCount;

    public function __construct(phpFlickr $f) {
        //set default
        $this->type = $this->types[0];
        $this->f = $f;
        $this->message = '';
        $this->committed = false;
        $this->searchCount = self::DEFAULT_COUNT;

    }

    public static function fixType($type) {
        if (in_array($type, self::$types))
            return $type;

        //set default type otherwise
        return self::$types[0];
    }

    public static function fixCount($count) {
        if (in_array($count, self::$counts))
            return $count;

        //otherwise
        return self::DEFAULT_COUNT;
    }

    public function setType($type) {
        $this->type = self::fixType($type);
    }

    

    public function searchByKeyword($text) {
        $args = array();
        $args['text'] = $text;
        $args['per_page'] = $this->getSearchCount();
        $args['extras'] = self::EXTRAS;

        if (empty($text)) {
            $this->setCommitted(false);
            $this->message = "<p>Input some keywords for this type of search, pls..</p>";
        } else {
            $result = $this->f->photos_search($args);
        }

        //debug
        //print "<pre>";
        //print_r($result);
        //print "</pre>";

        $this->setResult($result);
    }

    public function searchRecent() {

        $result = $this->f->photos_getRecent(self::EXTRAS, $this->getSearchCount());

        $this->setResult($result);
    }

    public function genericSearch() {
        $this->resetMessage();
        $this->setType($_REQUEST["searchType"]); //being fixed inside!
        $this->setSearchCount($_REQUEST["searchCount"]); //being fixed inside!
        $this->setCommitted(true);


        switch ($this->getType()) {
            default :
            case "text":
                $this->searchByKeyword($_REQUEST["input"]);
                break;

            case "latest":
                $this->searchRecent();
                break;
        }

        //all kinds of searches filled in result if committed succesfully.
        if ($this->isCommitted()) {
            $this->processResultIntoMedias();
        }
    }

    public function processResultIntoMedias() {
        if (empty($this->result)) { //on error or something
            $this->resultMedias = array();
            return 0;
        }

        //debug
        //print "<pre>";
        //print_r($this->result);
        //print "</pre>";
        //create Media for each photo and fill it with data

        foreach ($this->result["photo"] as $ph_args) {

            if ($ph_args["media"] == "photo") {
                //print "creating photo<br>";
                $p = new Photo($ph_args, $this->f);
            } else {
                  //print "creating video<br>";
                 $p = new Video($ph_args, $this->f);
            }
            //$p = new Media($ph_args, $this->f);
            

            //add the media to the list
            $this->addResultMedia($p);
        }
    }

    ///-------getters and setters and tiny helpers-------------

    public function setResult($result) {
        $this->result = $result;
    }

    public function getResult() {
        return $this->result;
    }

    public function clearResult() {
        $this->setResult("");
    }

    public function clearResultMedias() {
        $this->resultMedias = array();
    }

    public function getResultMediasCount() {
        return count($this->resultMedias);
    }

    public function addResultMedia(Media $p) {
        $this->resultMedias[] = $p;
    }

    public function getResultMedias() {
        return $this->resultMedias;
    }

    public function setResultMedias($result) {
        $this->resultMedias = $result;
    }

    public function getType() {
        return $this->type;
    }

    public function getMessage() {
        return $this->message;
    }

    public function setMessage($message) {
        $this->message = $message;
    }

    public function resetMessage() {
        $this->message = '';
    }

    public function isCommitted() {
        return $this->committed;
    }

    public function setCommitted($committed) {
        $this->committed = $committed;
    }

    public function getSearchCount() {
        return $this->searchCount;
    }

    public function setSearchCount($searchCount) {
        $this->searchCount = self::fixCount($searchCount);
    }

}

?>
