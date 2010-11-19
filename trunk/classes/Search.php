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
    private $type;
    private $result; //array thingy
    private $f; // flickr object
    private $resultPhotos = array(); //array of photos
    private $message;
    private $committed;
    private $local_geo;

    public function __construct(phpFlickr $f) {
        //set default
        $this->type = $this->types[0];
        $this->f = $f;
        $this->message = '';
        $this->committed = false;
        $this->local_geo = new Geo("", "", true); //invalid Geo for starters :)

      //  $this->local_geo = new Geo(0, 0); //invalid
    }

    public static function fixType($type) {
        $out = "";
        if (in_array($type, self::$types)) {
            //set chosen type
            $out = $type;
        } else {
            //set default type otherwise
            $out = self::$types[0];
        }
        return $out;
    }

    public function setType($type) {
        $this->type = self::fixType($type);
    }

    public function requestGeo() {
        $lat_req = $_REQUEST["geo_lat"];
        $long_req = $_REQUEST["geo_long"];

        //e.g.  2 00, 34 -> 200.34
        $lat_req = Helper::czFixFloat($lat_req);
        $long_req = Helper::czFixFloat($long_req);


       // print $lat_req . "---";
        //print $_REQUEST["geo_lat"] . "XX";
        $this->local_geo = new Geo($lat_req, $long_req);
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

    public function searchByKeyword($text, $count = self::DEFAULT_COUNT) {
        $args = array();
        $args['text'] = $text;
        $args['per_page'] = $count;
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

    public function searchRecent($count = self::DEFAULT_COUNT) {

        $result = $this->f->photos_getRecent(self::EXTRAS, $count);

        $this->setResult($result);
    }

    public function genericSearch($count = self::DEFAULT_COUNT) {
        $this->resetMessage();
        $this->setType($_REQUEST["searchType"]); //being fixed inside!
        $this->setCommitted(true);
        $this->requestGeo(); //being fixed inside and marked (in)valid

       
        switch ($this->getType()) {
            default :
            case "text":
                $this->searchByKeyword($_REQUEST["input"], $count);
                break;

            case "latest":
                $this->searchRecent($count);
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

    /**
     * return center of the search/rerank
     * @return Geo  position
     */
    public function getLocal_geo() {
        return $this->local_geo;
    }

    public function setLocal_geo($local_geo) {
        $this->local_geo = $local_geo;
    }

}

?>
