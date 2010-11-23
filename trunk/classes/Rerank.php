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
        "media_type_order"
    );
    private $type;
    public static $similarityTypes = array(
        "levenshtein",
        "similar_text",
        "lcs",
    );
    private $similarityType;
    private static $titleSimilarityPattern;
    private $views_point;
    public static $mediaTypeOrders = array(
        "photos_videos",
        "videos_photos",
    );
    private $mediaTypeOrder;

    public function __construct(Search $s) {
        $this->search = $s;
        $this->local_geo = new Geo("", "", true); //invalid Geo for starters :)
        $this->type = self::$types[0];
        $this->similarityType = self::$similarityTypes[0]; //levenshtein
        $this->views_point = 0;
        $this->mediaTypeOrder = self::$mediaTypeOrders[0];
    }

    public static function fixType($type) {
        if (in_array($type, self::$types))
            return $type;

        return self::$types[0];
    }

    public static function fixSimilarityType($stype) {
        if (in_array($stype, self::$similarityTypes))
            return $stype;
        return self::$similarityTypes[0];
    }

    public static function fixMediaTypeOrder($mtype) {
        if (in_array($mtype, self::$mediaTypeOrders))
            return $mtype;
        return self::$mediaTypeOrders[0];
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

            case "media_type_order":
                $this->rerankByMediaTypeOrder();
                break;
        }
    }

    //---------------rerank:geo/distance---------------------
    public function rerankByDistance() {
        $this->requestGeo(); //being fixed inside and marked (in)valid //TODO: presunout dovnitr assignMediasTo..
        if (!$this->local_geo->isValid()) {
            $this->search->setMessage("Cannot rerank by geo when local geo data are invalid.");
            $this->setCommitted(false);
            return;
        }

        $this->assignDistanceToMedias();

        $arr = $this->search->getResultMedias();

        //this means: for comparing Array of Objects use callback compare function Rerank::cmpByDistance (musi byt static)
        usort($arr, array("Rerank", "cmpByDistance"));
        $this->search->setResultMedias($arr);
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

    public function assignDistanceToMedias() {
        $array = $this->search->getResultMedias();
        foreach ($array as $p) {
            /* @var $p Media */
            $p->assignDistanceTo($this->getLocal_geo());
            //$this->assignDistanceToMedia($p);
        }
    }

    /**
     * Compares 2 medias by their distance, distance may not be valid at any media. Undefined distance is always bigger than defined.
     * This is an callback function, for more info see @link http://cz.php.net/usort example #3. 
     * @param Media $media1
     * @param Media $media2
     * @return 1 if >, -1 if <, 0 if =.
     */
    public static function cmpByDistance(Media $media1, Media $media2) {
        if (!$media1->getGeo()->isValid() && !$media2->getGeo()->isValid()) {
            //both not valid, same
            return 0;
        }

        if (!$media1->getGeo()->isValid()) {
            return 1; // invalid > valid
        }

        if (!$media2->getGeo()->isValid()) {
            return -1; // valid < invalid
        }

        if ($media1->getRrDistance() == $media2->getRrDistance()) {
            return 0;
        }

        if ($media1->getRrDistance() > $media2->getRrDistance()) {
            return 1;
        }

        return -1; // <
    }

    //---------------rerank:title---------------------
    public static function cmpByTitle(Media $media1, Media $media2) {
        //strtolower doesnt seem to work on nation-specific stuff
        // mb_strtolower()  is better
        $s1 = mb_strtolower($media1->getTitle());
        $s2 = mb_strtolower($media2->getTitle());
        return strcmp($s1, $s2);
    }

    public function rerankByTitle() {

        $arr = $this->search->getResultMedias();

        //this means: for comparing Array of Objects use callback compare function Rerank::cmpByDistance (musi byt static)
        usort($arr, array("Rerank", "cmpByTitle"));
        $this->search->setResultMedias($arr);
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

        $this->assignSimilarityToMedias();

        $arr = $this->search->getResultMedias(); //type is used inside

        if ($this->getSimilarityType() == self::$similarityTypes[0]) {
            //this means: for comparing Array of Objects use callback compare function Rerank::cmpByDistance (musi byt static)
            usort($arr, array("Rerank", "cmpByTitleSimilarity"));
        } else {
            usort($arr, array("Rerank", "cmpByTitleSimilarityReversed"));
        }
        $this->search->setResultMedias($arr);
    }

    public function assignSimilarityToMedias() {
        foreach ($this->search->getResultMedias() as $p) {
            /* @var $p Media */
            $p->assignTitleSimilarityTo($this->getTitleSimilarityPattern(), $this->getSimilarityType());
        }
    }

    public static function cmpByTitleSimilarity(Media $media1, Media $media2) {
        if ($media1->getTitleSimilarity() == $media2->getTitleSimilarity())
            return 0; //same

            if ($media1->getTitleSimilarity() > $media2->getTitleSimilarity())
            return 1; // >

            return -1; // <
    }

    /**
     * Reversed order of cmpByTitleSimilarity, used for version with similar_text, no need for this with levenshtein.
     * @param Media $media1
     * @param Media $media2
     * @return integer -1 for >, 0 for =, 1 for <
     */
    public static function cmpByTitleSimilarityReversed(Media $media1, Media $media2) {
        $res = self::cmpByTitleSimilarity($media1, $media2);

        return (-1) * $res;  // -1 => 1; 1=> -1, 0 zustava;
    }

    //---------------rerank:views---------------------
    public static function cmpByViews(Media $media1, Media $media2) {
        $s1 = $media1->getViews();
        $s2 = $media2->getViews();

        if ($s1 < $s2)
            return 1;
        if ($s1 > $s2)
            return -1;
        return 0; // =
    }

    public function rerankByViews() {

        $arr = $this->search->getResultMedias();

        usort($arr, array("Rerank", "cmpByViews"));
        $this->search->setResultMedias($arr);
    }

    //---------------rerank:views_diff---------------------

    public function assignViewsDiffToMedias() {
        foreach ($this->search->getResultMedias() as $p) {
            /* @var $p Media */
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

        $this->assignViewsDiffToMedias();

        $arr = $this->search->getResultMedias(); //type is used inside


        usort($arr, array("Rerank", "cmpByViewsDiffReversed"));

        $this->search->setResultMedias($arr);
    }

    public static function cmpByViewsDiff(Media $media1, Media $media2) {

        $s1 = $media1->getViewsDiff();
        $s2 = $media2->getViewsDiff();

        if ($s1 < $s2)
            return 1;
        if ($s1 > $s2)
            return -1;
        return 0; // =
    }

    public static function cmpByViewsDiffReversed(Media $media1, Media $media2) {
        return self::cmpByViewsDiff($media1, $media2) * (-1);
    }

    //----------------rerank by media order type---------------
    public function rerankByMediaTypeOrder() {
        $this->setMediaTypeOrder($_REQUEST["media_type_order"]); //fixed inside

        $arr = $this->search->getResultMedias();

        if ($this->mediaTypeOrder == "photos_videos") {
            usort($arr, array("Rerank", "cmpByMediaTypeOrder"));
        } else {
            usort($arr, array("Rerank", "cmpByMediaTypeOrderReversed"));
        }
        
        $this->search->setResultMedias($arr);
    }

    public static function cmpByMediaTypeOrder(Media $media1, Media $media2) {

        $s1 = $media1->getMediaType();
        $s2 = $media2->getMediaType();

        if ($s1 == $s2)
            return 0; //=

        if ($s1 == "video")
            return 1;
        //otherwise
        return -1;
    }

    public static function cmpByMediaTypeOrderReversed(Media $media1, Media $media2) {
        return self::cmpByMediaTypeOrder($media1, $media2) * (-1);
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

    public function getMediaTypeOrder() {
        return $this->mediaTypeOrder;
    }

    public function setMediaTypeOrder($mediaTypeOrder) {
        $this->mediaTypeOrder = self::fixMediaTypeOrder($mediaTypeOrder);
    }

}

?>
