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
    public static $types = array(
        "title",
        "title_similarity",
        "views",
        "geo",
    );
    private $type;
    private static $titleSimilarityPattern;

    public function __construct(Search $s) {
        $this->search = $s;
        $this->local_geo = new Geo("", "", true); //invalid Geo for starters :)
        $this->type = self::$types[0];
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

    public function genericRerank() {
        $this->setType($_REQUEST["rerankType"]);
        $this->setCommitted(true); //by default: it'll be ok

        switch ($this->getType()) {
            default:
            case "title":
                $this->rerankByTitle();
                break;

            case "title_similarity":
                $this->rerankByTitleSimilarity();
                break;
            
            case "views":
                $this->rerankByViews();
                break;

            case "geo":
                $this->rerankByDistance();
                break;
        }
    }

    //---------------rerank:geo/distance---------------------
    public function rerankByDistance() {
        $this->requestGeo(); //being fixed inside and marked (in)valid
        if (!$this->local_geo->isValid()) {
            $this->search->setMessage("Cannot rerank by geo when local geo data are invalid.");
            $this->setCommitted(false);
            return;
        }

        $this->assignDistanceToPhotos();

        $arr = $this->search->getResultPhotos();

        //this means: for comparing Array of Objects use callback compare function Rerank::cmpByDistance (musi byt static)
        usort($arr, array("Rerank", "cmpByDistance"));
        $this->search->setResultPhotos($arr);
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

    //---------------rerank:title---------------------
    public static function cmpByTitle(Photo $photo1, Photo $photo2) {
        //strtolower doesnt seem to work on nation-specific stuff
        // mb_strtolower()  is better
        $s1 = mb_strtolower($photo1->getTitle());
        $s2 = mb_strtolower($photo2->getTitle());
        return strcmp($s1, $s2);
    }

    public function rerankByTitle() {

        $arr = $this->search->getResultPhotos();

        //this means: for comparing Array of Objects use callback compare function Rerank::cmpByDistance (musi byt static)
        usort($arr, array("Rerank", "cmpByTitle"));
        $this->search->setResultPhotos($arr);
    }

    //---------------rerank:title similarity---------------------
    public function rerankByTitleSimilarity() {
        $sp_req = trim($_REQUEST["title_similarity_pattern"]);
        if (empty($sp_req)) {

            $this->search->setMessage("Cannot rerank by title similarity with no pattern.");
            $this->setCommitted(false);
            return;
        }
        $this->setTitleSimilarityPattern($sp_req);

        $this->assignSimilarityToPhotos();

        $arr = $this->search->getResultPhotos();

        //this means: for comparing Array of Objects use callback compare function Rerank::cmpByDistance (musi byt static)
        usort($arr, array("Rerank", "cmpByTitleSimilarityReversed"));
        $this->search->setResultPhotos($arr);
    }

    public function assignSimilarityToPhotos() {
        foreach ($this->search->getResultPhotos() as $p) {
            /* @var $p Photo */
            $p->assignTitleSimilarityTo($this->getTitleSimilarityPattern());
        }
    }

    public static function cmpByTitleSimilarity(Photo $photo1, Photo $photo2) {
        if ($photo1->getTitleSimilarity() == $photo2->getTitleSimilarity())
            return 0; //same

            if ($photo1->getTitleSimilarity() > $photo2->getTitleSimilarity())
            return 1; // >

            return -1; // <
    }

    public static function cmpByTitleSimilarityReversed(Photo $photo1, Photo $photo2) {
        $res = self::cmpByTitleSimilarity($photo1, $photo2);

        return (-1) * $res;  // -1 => 1; 1=> -1, 0 zustava;
    }

     //---------------rerank:views---------------------
    public static function cmpByViews(Photo $photo1, Photo $photo2) {
        $s1 = $photo1->getViews();
        $s2 = $photo2->getViews();
        
        if ($s1 < $s2) return 1;
        if ($s1 > $s2) return -1;
        return 0; // =
    }

    public function rerankByViews() {

        $arr = $this->search->getResultPhotos();

        usort($arr, array("Rerank", "cmpByViews"));
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

    public function getType() {
        return $this->type;
    }

    public function setType($type) {
        $this->type = self::fixType($type);
    }

    public function getTitleSimilarityPattern() {
        return self::$titleSimilarityPattern;
    }

    public function setTitleSimilarityPattern($titleSimilarityPattern) {
        self::$titleSimilarityPattern = $titleSimilarityPattern;
    }

}

?>
