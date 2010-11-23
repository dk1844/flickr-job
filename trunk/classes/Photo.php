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

    /**
     * Finds out the length of the largest common substring from 2 strings, we dont care what it acually is :)
     * Adapted from @link http://en.wikibooks.org/wiki/Algorithm_implementation/Strings/Longest_common_substring#PHP
     * The above mentioned function was able to return all the longest substring, whereas we only need the length, so i simplified it a bit.
     * @param string $str1 first string
     * @param string $str2 second string
     * @return int the length of lcs
     */
   public static function lcsLength($str1, $str2){
	$str1Len = strlen($str1);
	$str2Len = strlen($str2);
	//$ret = array();

	if($str1Len == 0 || $str2Len == 0)
	return 0; //no similarities

	$CSL = array(); //Common Sequence Length array
	$intLargestSize = 0;

	//initialize the CSL array to assume there are no similarities
	for($i=0; $i<$str1Len; $i++){
		$CSL[$i] = array();
		for($j=0; $j<$str2Len; $j++){
			$CSL[$i][$j] = 0;
		}
	}

	for($i=0; $i<$str1Len; $i++){
		for($j=0; $j<$str2Len; $j++){
			//check every combination of characters
			if( $str1[$i] == $str2[$j] ){
				//these are the same in both strings
				if($i == 0 || $j == 0)
					//it's the first character, so it's clearly only 1 character long
					$CSL[$i][$j] = 1;
				else
					//it's one character longer than the string from the previous character
					$CSL[$i][$j] = $CSL[$i-1][$j-1] + 1;

				if( $CSL[$i][$j] > $intLargestSize ){
					//remember this as the largest
					$intLargestSize = $CSL[$i][$j];
					
				}
				
			}
			//else, $CSL should be set to 0, which it was already initialized to
		}
	}
	return $intLargestSize;
}



    public static function calcTitleSimilarity($title1, $title2, $type = "levenshtein") {
        if ( (empty($title1) || empty($title2)) && $type != "levenshtein")
            return 0;

        if ($type == "similar_text")
            return similar_text(mb_strtolower($title1), mb_strtolower($title2)); //to lowercase;
        if ($type == "lcs")
            return self::lcsLength(mb_strtolower($title1), mb_strtolower($title2));
        //otherwise
            return levenshtein(mb_strtolower($title1), mb_strtolower($title2));   //should be faster than similar_text, has been adviced in a consult :)
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
