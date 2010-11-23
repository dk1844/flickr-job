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
    const EXTRAS = 'description, license, date_upload, date_taken,
        owner_name, last_update, media, geo, tags, views, url_t';

    // original_format,
    //available search types, 0 is default
    //media rozlisovat?
    public static $types = array(
        "text",
        "latest",
    ); // "date",  "popular",
    public static $counts = array(
        1, 2, 5,
        10, 20, 50,
        75, 100,
    );
    private $type;
    private $result; //array thingy
    private $f; // flickr object
    private $resultPhotos = array(); //array of photos
    private $message;
    private $committed;
    private $searchCount;

    public function __construct(phpFlickr $f) {
        //set default
        $this->type = $this->types[0];
        $this->f = $f;
        $this->message = '';
        $this->committed = false;
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

    public function searchByDate($from = '', $to ='', $count = self::DEFAULT_COUNT) {
        $args = array();

        if (empty($from) && empty($to)) {
            $args['min_upload_date'] = date("U") - 10 * 60; // last 10 min
        } else {
            if (!empty($from))
                $args['min_upload_date'] = $from;
            if (!empty($to))
                $args['max_upload_date'] = $to;
        }

        $args['per_page'] = $count;
        $args['extras'] = self::EXTRAS;

        $result = $this->f->photos_search($args);
        $this->setResult($result);
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
            $this->processResultIntoPhotos();
        }
    }

    public function processResultIntoPhotos() {
        if (empty($this->result)) { //on error or something
            $this->resultPhotos = array();
            return 0;
        }

        //debug
        //print "<pre>";
        //print_r($this->result);
        //print "</pre>";
        //create Photo for each photo and fill it with data
        foreach ($this->result["photo"] as $ph_args) {
            $p = new Photo($ph_args, $this->f);
            $p->setData($ph_args);

            //add the photo to the list
            $this->addResultPhoto($p);
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

    public function clearResultPhotos() {
        $this->resultPhotos = array();
    }

    public function getResultPhotosCount() {
        return count($this->resultPhotos);
    }

    public function addResultPhoto(Photo $p) {
        $this->resultPhotos[] = $p;
    }

    public function getResultPhotos() {
        return $this->resultPhotos;
    }

    public function setResultPhotos($result) {
        $this->resultPhotos = $result;
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
