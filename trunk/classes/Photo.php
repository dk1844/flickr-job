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
class Photo extends Media {

   


    public function __construct($args, phpFlickr $f) {
        parent::__construct($args, $f);
        $this->setMediaType("photo");

        $this->createPageDirectUrl(); //sets pageUrl & directUrl in ancestor
    }

   
     //overidy/implementy

    public function createPageDirectUrl() {
        $sizes_response = $this->getF()->photos_getSizes($this->getId());

        //print "<pre>";
        //print_r($sizes_response);
        //print "</pre>";

        $largest_index = count($sizes_response) - 1;
        // e.g. :
        //    [source] => http://farm6.static.flickr.com/5005/5201963665_6049cec884_o.jpg
        //    [url] => http://www.flickr.com/photos/moonsun/5201963665/sizes/o/
        //                                                            /sizes/sq/ for [0]
        $direct_fullsize = $sizes_response[$largest_index]["source"];

        // \/sizes\/sq\/$
        // ... 5201963665/sizes/sq/ -> 5201963665/
        $pattern = "/\/sizes\/sq\/$/i";

        $pageUrl = preg_replace($pattern, "/", $sizes_response[0]["url"] );


        $this->setDirectUrl($direct_fullsize);
        $this->setPageUrl($pageUrl);

    }


    
}
?>
