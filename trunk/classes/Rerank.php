<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Rerank
 *
 * @author Daniel
 */
class Rerank {

    private $search;
    private $local_geo;
    private $committed;

    public function __construct(Search $s) {
        $this->search = $s;
        $this->local_geo = new Geo("", "", true); //invalid Geo for starters :)
    }

    public function requestGeo() {
        $lat_req = $_REQUEST["geo_lat"];
        $long_req = $_REQUEST["geo_long"];

        //e.g.  2 00, 34 -> 200.34
        $lat_req = Helper::czFixFloat($lat_req);
        $long_req = Helper::czFixFloat($long_req);


        // print $lat_req . "---";
        //print $_REQUEST["geo_lat"] . "XX";
        $this->local_geo = new Geo($lat_req, $long_req); //may not be valid
    }

    public function genericRerank() {
        //tryout
        $this->setCommitted(true);
        $this->rrByDistance();
    }

    public function assignDistanceToPhoto(Photo $p) {
        //at this point local_geo is valid
        if ($p->getGeo()->isValid()) {
            $distance = Geo::calcDistance($this->getLocal_geo(), $p->getGeo());
            $p->setRrDistance($distance);
        }
    }

    public function assignDistanceToPhotos() {
        $array = $this->search->getResultPhotos();
        foreach ($array as $p) {
            $this->assignDistanceToPhoto($p);
        }
    }

    /**
     * Compares 2 photos by their distance, distance may not be valid at any photo. Undefined distance is always bigger than defined.
     * This is an callback function, for more info see @link http://cz.php.net/usort example #3. 
     * @param Photo $photo1
     * @param Photo $photo2
     * @return 1 if >, -1 if <, 0 if =.
     */
    public static function cmpByDistance(Photo $photo1, Photo $photo2) {
        if (!$photo1->getGeo()->isValid() && !$photo2->getGeo()->isValid()) {
            //both not valid, same
            return 0;
        }

        if (!$photo1->getGeo()->isValid()) {
            return 1; // invalid > valid
        }

        if (!$photo2->getGeo()->isValid()) {
            return -1; // valid < invalid
        }

        if ($photo1->getRrDistance() == $photo2->getRrDistance()) {
            return 0;
        }

        if ($photo1->getRrDistance() > $photo2->getRrDistance()) {
            return 1;
        }
        
        return -1; // <

    }

    public function rrByDistance($order = "asc") {
        $this->requestGeo(); //being fixed inside and marked (in)valid
        if (!$this->local_geo->isValid()) {
            $this->search->setMessage("Cannot rerank by geo when local geo data are invalid.");
            $this->setCommitted(false);
            return;
        }

        $this->assignDistanceToPhotos();

        $arr = $this->search->getResultPhotos();

        usort($arr, array("Rerank", "cmpByDistance"));
        $this->search->setResultPhotos($arr);
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

        public function isCommitted() {
        return $this->committed;
    }

    public function setCommitted($committed) {
        $this->committed = $committed;
    }


}

?>
