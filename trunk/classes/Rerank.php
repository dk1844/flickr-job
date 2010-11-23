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
        "views_diff",
        "geo",
    );
    private $type;
    public static $similarityTypes = array(
        "levenshtein",
        "similar_text",
    );
    private $similarityType;
    private static $titleSimilarityPattern;
    private $views_point;

    public function __construct(Search $s) {
        $this->search = $s;
        $this->local_geo = new Geo("", "", true); //invalid Geo for starters :)
        $this->type = self::$types[0];
        $this->similarityType = self::$similarityTypes[0]; //levenshtein
        $this->views_point = 0;
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

    public static function fixSimilarityType($stype) {
        $out = "";
        if (in_array($stype, self::$similarityTypes)) {
            //set chosen type
            $out = $stype;
        } else {
            //set default type otherwise
            $out = self::$similarityTypes[0];
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

            case "views_diff":
                $this->rerankByViewsDiff();
                break;

            case "geo":
                $this->rerankByDistance();
                break;
        }
    }

    //---------------rerank:geo/distance---------------------
    public function rerankByDistance() {
        $this->requestGeo(); //being fixed inside and marked (in)valid //TODO: presunout dovnitr assignPhotosTo..
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

 
    public function assignDistanceToPhotos() {
        $array = $this->search->getResultPhotos();
        foreach ($array as $p) {
            /* @var $p Photo*/
            $p->assignDistanceTo($this->getLocal_geo());
            //$this->assignDistanceToPhoto($p);
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
        $this->setSimilarityType($_REQUEST["similarity_type"]);
        $this->setTitleSimilarityPattern($sp_req);

        $this->assignSimilarityToPhotos();

        $arr = $this->search->getResultPhotos(); //type is used inside

        if ($this->getSimilarityType() == self::$similarityTypes[0]) {
            //this means: for comparing Array of Objects use callback compare function Rerank::cmpByDistance (musi byt static)
            usort($arr, array("Rerank", "cmpByTitleSimilarity"));
        } else {
            usort($arr, array("Rerank", "cmpByTitleSimilarityReversed"));
        }
        $this->search->setResultPhotos($arr);
    }

    public function assignSimilarityToPhotos() {
        foreach ($this->search->getResultPhotos() as $p) {
            /* @var $p Photo */
            $p->assignTitleSimilarityTo($this->getTitleSimilarityPattern(), $this->getSimilarityType());
        }
    }

    public static function cmpByTitleSimilarity(Photo $photo1, Photo $photo2) {
        if ($photo1->getTitleSimilarity() == $photo2->getTitleSimilarity())
            return 0; //same

            if ($photo1->getTitleSimilarity() > $photo2->getTitleSimilarity())
            return 1; // >

            return -1; // <
    }

    /**
     * Reversed order of cmpByTitleSimilarity, used for version with similar_text, no need for this with levenshtein.
     * @param Photo $photo1
     * @param Photo $photo2
     * @return integer -1 for >, 0 for =, 1 for <
     */
    public static function cmpByTitleSimilarityReversed(Photo $photo1, Photo $photo2) {
        $res = self::cmpByTitleSimilarity($photo1, $photo2);

        return (-1) * $res;  // -1 => 1; 1=> -1, 0 zustava;
    }

    //---------------rerank:views---------------------
    public static function cmpByViews(Photo $photo1, Photo $photo2) {
        $s1 = $photo1->getViews();
        $s2 = $photo2->getViews();

        if ($s1 < $s2)
            return 1;
        if ($s1 > $s2)
            return -1;
        return 0; // =
    }

    public function rerankByViews() {

        $arr = $this->search->getResultPhotos();

        usort($arr, array("Rerank", "cmpByViews"));
        $this->search->setResultPhotos($arr);
    }

    //---------------rerank:views_diff---------------------

    public function assignViewsDiffToPhotos() {
        foreach ($this->search->getResultPhotos() as $p) {
            /* @var $p Photo */
            $p->assignViewsDiffTo($this->getViews_point());
        }
    }

    public function rerankByViewsDiff() {
        $vd_req = $_REQUEST["views_point"];
        if (empty($vd_req) || !ctype_digit($vd_req)) {

            $this->search->setMessage("Cannot rerank by view # closeness with no value");
            $this->setCommitted(false);
            return;
        }

        $this->setViews_point($_REQUEST["views_point"]);

        $this->assignViewsDiffToPhotos();

        $arr = $this->search->getResultPhotos(); //type is used inside


        usort($arr, array("Rerank", "cmpByViewsDiffReversed"));

        $this->search->setResultPhotos($arr);
    }



    public static function cmpByViewsDiff(Photo $photo1, Photo $photo2) {

        $s1 = $photo1->getViewsDiff();
        $s2 = $photo2->getViewsDiff();

        if ($s1 < $s2)
            return 1;
        if ($s1 > $s2)
            return -1;
        return 0; // =
    }


     public static function cmpByViewsDiffReversed(Photo $photo1, Photo $photo2) {
         return self::cmpByViewsDiff($photo1, $photo2)*(-1);
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

    public function getSimilarityType() {
        return $this->similarityType;
    }

    public function setSimilarityType($similarityType) {
        $this->similarityType = self::fixSimilarityType($similarityType);
    }

    public function getTitleSimilarityPattern() {
        return self::$titleSimilarityPattern;
    }

    public function setTitleSimilarityPattern($titleSimilarityPattern) {
        self::$titleSimilarityPattern = $titleSimilarityPattern;
    }

    public function getViews_point() {
        return $this->views_point;
    }

    public function setViews_point($views_point) {
        $this->views_point = $views_point;
    }

}

?>
