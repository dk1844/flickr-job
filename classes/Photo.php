<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Photo
 *
 * @author Daniel
 */
class Photo {

    private $f; //flickPhp thingy
    private $id;
    private $owner_id;
    private $title;
    private $description;
    private $ownerName;
    private $views;
    private $tags;
    private $geo;
    private $thumbnail_url;
    private $fullsize_url;
    // rr vars
    private $rrDistance;
    private $titleSimilarity;
    private $viewsDiff;


    /*
      [license] => 0
      [lastupdate] => 1289875610
      [tags] =>
      [media] => photo
      [media_status] => ready
     */

    public function __construct($args, phpFlickr $f) {
        $this->f = $f;
        $this->setData($args);
    }

    /*
      (
      [id] => 5182048959
      [owner] => 17413675@N04
      [secret] => b7abfdb78d
      [server] => 1293
      [farm] => 2
      [title] => asdf
      [ispublic] => 1
      [isfriend] => 0
      [isfamily] => 0
      [description] =>
      [license] => 0
      [dateupload] => 1289936490
      [datetaken] => 2010-11-16 11:41:30
      [datetakengranularity] => 0
      [ownername] => cesiliabustos
      [lastupdate] => 1289936570
      [latitude] => 0
      [longitude] => 0
      [accuracy] => 0
      [tags] =>
      [views] => 2
      [url_t] => http://farm2.static.flickr.com/1293/5182048959_b7abfdb78d_t.jpg
      [height_t] => 70
      [width_t] => 100
      )
     */

    public function setData($args) {
        $this->id = $args["id"];
        $this->owner_id = $args["owner"];
        $this->title = $args["title"];
        $this->description = $args["description"];
        $this->ownerName = $args["ownername"];
        $this->views = $args["views"];
        $this->tags = $args["tags"];

        // acc = 0 => this means no geo is given by the author => invalid geo ( , , true)
        $this->geo = new Geo($args["latitude"], $args["longitude"], $args["accuracy"] == 0);

        $this->thumbnail_url = $args["url_t"];
        $this->fullsize_url = $this->createFullsizeUrl();
    }

    /**
     * As we cannot be sure how many sizes of a picture there are, this method is sure to return the largest one's url
     * @return string url of the FullSize pic
     */
    public function createFullsizeUrl() {
        $sizes_response = $this->f->photos_getSizes($this->id);

        $largest_index = count($sizes_response) - 1;
        $fullUrl = $sizes_response[$largest_index]["source"];

        return $fullUrl;
    }

    public static function calcTitleSimilarity($title1, $title2, $type = "levenshtein") {
        if (empty($title1) || empty($title2))
            return 0;

        if ($type == "similar_text")
            return similar_text(mb_strtolower($title1), mb_strtolower($title2)); //to lowercase;

            return levenshtein(mb_strtolower($title1), mb_strtolower($title2));   //should be faster, has been adviced in a consult :)
    }

    public function calcTitleSimilarityTo($otherTitle, $type = "levenshtein") {
        $this_title = $this->getTitle();

        return Photo::calcTitleSimilarity($this->getTitle(), $otherTitle, $type);
    }

    public function assignTitleSimilarityTo($otherTitle, $type = "levenshtein") {
        $this->setTitleSimilarity($this->calcTitleSimilarityTo($otherTitle, $type));
    }

     public function assignDistanceTo(Geo $otherPlace) {
        if ($this->getGeo()->isValid()) {
            $distance = Geo::calcDistance($otherPlace,$this->getGeo());
            $this->setRrDistance($distance);
        }
    }



    public function assignViewsDiffTo($views_point) {
        $viewsDiff = abs($this->views-$views_point);
        $this->setViewsDiff($viewsDiff);
    }


///-------getters and setters-------------

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getOwnerId() {
        return $this->owner_id;
    }

    public function setOwnerId($owner_id) {
        $this->owner_id = $owner_id;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function getOwnerName() {
        return $this->ownerName;
    }

    public function setOwnerName($ownerName) {
        $this->ownerName = $ownerName;
    }

    public function getViews() {
        return $this->views;
    }

    public function setViews($views) {
        $this->views = $views;
    }

    public function getTags() {
        return $this->tags;
    }

    public function setTags($tags) {
        $this->tags = $tags;
    }

    /**
     * returns a Geo object
     * @return Geo position
     */
    public function getGeo() {
        return $this->geo;
    }

    /**
     * Assign Geo object
     * @param Geo $geo  position
     */
    public function setGeo($geo) {
        $this->geo = $geo;
    }

    public function getThumbnailUrl() {
        return $this->thumbnail_url;
    }

    public function setThumbnailUrl($thumbnail_url) {
        $this->thumbnail_url = $thumbnail_url;
    }

    public function getFullsizeUrl() {
        return $this->fullsize_url;
    }

    public function setFullsizeUrl($fullsize_url) {
        $this->fullsize_url = $fullsize_url;
    }

    public function getRrDistance() {
        return $this->rrDistance;
    }

    public function setRrDistance($rrDistance) {
        $this->rrDistance = $rrDistance;
    }

    public function getTitleSimilarity() {
        return $this->titleSimilarity;
    }

    public function setTitleSimilarity($titleSimilarity) {
        $this->titleSimilarity = $titleSimilarity;
    }

    public function getViewsDiff() {
        return $this->viewsDiff;
    }

    public function setViewsDiff($viewsDiff) {
        $this->viewsDiff = $viewsDiff;
    }

}

?>
