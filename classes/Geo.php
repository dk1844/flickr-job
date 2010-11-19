<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Geo
 *
 * @author Daniel
 */
class Geo {

    private $valid = false;
    private $latitude;
    private $longitude;

    public function __construct($latitude, $longitude, $invalidByDefalut = false) {
        
        if($invalidByDefalut) {
            $this->valid = false;
            $this->latitude = "";
            $this->longitude = "";
            return;
        }

        //otherwise
        if (Geo::isLatitudeValid($latitude) && Geo::isLongitudeValid($longitude)) {
            $this->valid = true;
            $this->latitude = $latitude;
            $this->longitude = $longitude;
        } else {
            $this->valid = false;
            $this->latitude = "";
            $this->longitude = "";
        }
    }

   
    /**
     * Calculates geographical distance of two places in km/mi
     * Based on @link http://sgowtham.net/blog/2009/08/04/php-calculating-distance-between-two-locations-given-their-gps-coordinates/
     * @param Geo $place1 place1
     * @param Geo $place2 place2
     * @param string $unit optional [km|mi], default km, if anything else given => km
     * @return real distance in unit
     */
    public static function calcDistance(Geo $place1, Geo $place2, $unit = "km") {
        $earth_radius = 3960.00; # in miles

        $lat_1 = $place1->getLatitude();
        $lon_1 = $place1->getLongitude();
        $lat_2 = $place2->getLatitude();
        $lon_2 = $place2->getLongitude();
        $delta_lat = $lat_2 - $lat_1;
        $delta_lon = $lon_2 - $lon_1;

        

        $distance = sin(deg2rad($lat_1)) * sin(deg2rad($lat_2)) + cos(deg2rad($lat_1)) * cos(deg2rad($lat_2)) * cos(deg2rad($delta_lon));
        $distance = acos($distance);
        $distance = rad2deg($distance);
        $distance = $distance * 60 * 1.1515;
        

        if ($unit != "mi") {
             $distance /= 0.621371192237; // mi->km:  1km = .6213mi
        }
        $distance = round($distance, 4);
        return $distance;
    }

    public function calcDistanceFrom(Geo $otherPlace, $unit = "km") {
        return self::calcDistance($this, $otherPlace, $unit);
    }

    /**
     * gives you all geo data at once
     * @return array (valid, longitude, latitude)
     */
    public function getGeoDataArray() {
        $res = array(
            'valid' => $this->valid,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        );
        return $res;
    }

    public static function isLatitudeValid($lat_str) {
        if (!is_numeric($lat_str))
            return false;
        if (floatval($lat_str) > 90)
            return false;
        if (floatval($lat_str) < -90)
            return false;
        return true;
    }

    public static function isLongitudeValid($long_str) {
        if (!is_numeric($long_str))
            return false;
        if (floatval($long_str) > 180)
            return false;
        if (floatval($long_str) < -180)
            return false;
        return true;
    }

    //---gettery
    public function getLatitude() {
        return $this->latitude;
    }

    public function getLongitude() {
        return $this->longitude;
    }

    public function isValid() {
        return $this->valid;
    }

    public function setValid() {
        $this->valid = true;
    }

    public function setInvalid() {
        $this->valid = false;
    }

}

?>
