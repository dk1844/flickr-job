<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Dimensions
 *
 * @author Daniel
 */
class Dimensions {

    private $height;
    private $width;
    private $area;
    private $name;

    public function __construct($w, $h, $name = "Uknown resolution") {
        $this->width = $w;
        $this->height = $h;
        $this->area = self::calcArea($w, $h);
        $this->name = $name;
    }

    public static function calcArea($w, $h) {
        return $w * $h;
    }

    //-----getters/setters  :)

    public function getHeight() {
        return $this->height;
    }

    public function setHeight($height) {
        $this->height = $height;
    }

    public function getWidth() {
        return $this->width;
    }

    public function setWidth($width) {
        $this->width = $width;
    }

    public function getArea() {
        return $this->area;
    }

    public function setArea($area) {
        $this->area = $area;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

}

?>
