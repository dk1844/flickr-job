<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Video
 *
 * @author Daniel
 */
class Video extends Media {

    public function __construct($args, phpFlickr $f) {
        parent::__construct($args, $f);
        $this->setMediaType("video");
        $this->createPageDirectUrl(); //sets pageUrl & directUrl in ancestor
    }

    //overidy/implementy

    public function createPageDirectUrl() {

        $sizes_response = $this->getF()->photos_getSizes($this->getId());
        // e.g. :
        //    [source] => http://farm6.static.flickr.com/5005/5201963665_6049cec884_o.jpg
        //    [url] => http://www.flickr.com/photos/moonsun/5201963665/sizes/o/
        //                                                            /sizes/sq/ for [0]
        $pattern = "/\/sizes\/sq\/$/i";
        $pageUrl = preg_replace($pattern, "/", $sizes_response[0]["url"]);
        $this->setPageUrl($pageUrl);

        //print "<pre>";
        //print_r($sizes_response);
        //print "</pre>";

        // we need the first array with media = video;

        $i = 0;
        while ($sizes_response[$i]["media"] != "video") {
            $i++;
        }

        //like [source] => http://www.flickr.com/apps/video/stewart.swf?v=71377&photo_id=5202801740&photo_secret=ea7046c255
        $video_url = $sizes_response[$i]["source"];
        
        
        $this->setDirectUrl($video_url);
    }

}

?>
