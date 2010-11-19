<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UI
 *
 * @author Daniel
 */
class UI {

    
    private $searchInput = '';
    private $searchInputType;
    private $searchCommited;
    private $searchOutput;
    private $page;
    private $message;

    //to be defined somewhere else or loaded from ..?
    const PAGE_TPL_LOCATION = "./tpl/page.tpl.xhtml";
    private $pageTpl;
    const INPUT_TPL_LOCATION = "./tpl/inputBlock.tpl.xhtml";
    private $inputTpl;

    public function __construct() {
        $this->resetUI();
        $this->loadTemplates();
    }

    public function fillUI($input, $type, $commited, $output, $message) {
        $this->setSearchInput($input);
        $this->setSearchInputType($type);
        $this->setSearchCommited($commited);
        $this->setSearchOutput($output);
        $this->setMessage($message);
    }

    public function resetUI() {
        $this->fillUI('', "text", false, "", "");
    }

    public function loadTemplates() {
        $this->pageTpl = file_get_contents(self::PAGE_TPL_LOCATION);
        $this->inputTpl = file_get_contents(self::INPUT_TPL_LOCATION);
    }

//--
//    public function buildUIold($input, $type, $commited, $output, $message) {
//        $this->fillUI($input, $type, $commited, $output, $message);
//
//        //get the page
//       $inputBlock = $this->inputTpl;
//        $inputBlock = str_replace("{inputText}", htmlspecialchars($input), $inputBlock);
//        $inputBlock = str_replace("{searchTypeSelector}", $this->createSearchTypeSelector($type), $inputBlock);
//
//        $page = $this->pageTpl;
//        $page = str_replace("{inputBlock}", $inputBlock, $page);
//        $page = str_replace("{outputBlock}", $output, $page);
//        $page = str_replace("{message}", $message,  $page);
//
//        $this->page = $page;
//    }

    public function buildUI(Search $ms) {
        $inputBlock = $this->inputTpl;
        $inputBlock = str_replace("{inputText}", htmlspecialchars($_REQUEST["input"]), $inputBlock);
        $inputBlock = str_replace("{searchTypeSelector}", $this->createSearchTypeSelector($_REQUEST["searchType"]), $inputBlock);

        $inputBlock = str_replace("{geo_lat}", $ms->getLocal_geo()->getLatitude(), $inputBlock);
        $inputBlock = str_replace("{geo_long}", $ms->getLocal_geo()->getLongitude(), $inputBlock);



        $page = $this->pageTpl;
        $page = str_replace("{inputBlock}", $inputBlock, $page); //as seen above :)
        $page = str_replace("{message}", $ms->getMessage(), $page);

        if ($ms->isCommitted()) {
            //generate output
            $output_imgs = $this->createAImgsTable($ms->getResultPhotos(), $ms);
            $heading = "<h1>Here it is:</h1>\n";
            $output = $heading . $output_imgs;

            $page = str_replace("{outputBlock}", $output, $page);
        } else {
            $initMsg = "<p>Start the search!</p>";
            $page = str_replace("{outputBlock}", $initMsg, $page);
        }
        $page = str_replace("{outputBlock}", $output, $page);

        $this->page = $page;
    }

    public function createAImg(Photo $p, Search $ms) {
        $out = "";

        $img = "<img src=\"" . $p->getThumbnailUrl() . "\" />";
        $out .= "<a href=\"" . $p->getFullsizeUrl() . "\" >" . $img . "</a><br/>\n";


        
        $lat = $p->getGeo()->getLatitude();
        $long = $p->getGeo()->getLongitude();

        if ($p->getGeo()->isValid() && $ms->getLocal_geo()->isValid()) { //both local & picture geo valid
            $out .= "geo={lat=$lat;long=$long}<br />";
            $out .= "Distance = " . $p->getGeo()->calcDistanceFrom($ms->getLocal_geo()) . "km";
        }

        return $out;
    }

    public function createAImgsTable($array, Search $ms) {
        $out = "";
        $table_w = 3;

        if (empty($array))
            return "<p> Search result empty, try different keywords or so.. </p>";


        $out .= "<table style=\"border:1px dotted maroon;\"><tr>";

        for ($index = 0; $index < count($array); $index++) {
            $one = $array[$index];

            if ($index % $table_w == 0 && $index != 0 && $index + 1 != count($array)) { //nasobek, neni 0. ani posledni
                $out .= "</tr>\n<tr>";
            }

            $out .= "<td>";
            $out .= $this->createAImg($one, $ms) . "\n";
            $out .= "</td>\n";
        }
        $out .= "</tr>\n</table>\n";

        return $out;
    }

    public function createSearchTypeSelector($selected) {
        $values = Search::$types;
        $out = "<select name=\"searchType\">\n";

        foreach ($values as $value) {
            $add = "";
            if ($value == $selected) {
                $add = " selected";
            }
            $out .= "<option value=\"$value\"$add>$value</option>";
        }
        $out .= "</select>";
        return $out;
    }

    //------------setters, getters

    public function getSearchInput() {
        return $this->searchInput;
    }

    public function setSearchInput($searchInput) {
        $this->searchInput = $searchInput;
    }

    public function getSearchInputType() {
        return $this->searchInputType;
    }

    public function setSearchInputType($searchInputType) {
        $this->searchInputType = $searchInputType;
    }

    public function isSearchCommited() {
        return $this->searchCommited;
    }

    public function setSearchCommited($searchCommited) {
        $this->searchCommited = $searchCommited;
    }

    public function getSearchOutput() {
        return $this->searchOutput;
    }

    public function setSearchOutput($searchOutput) {
        $this->searchOutput = $searchOutput;
    }

    public function getPage() {
        return $this->page;
    }

    public function setPage($page) {
        $this->page = $page;
    }

    public function setMessage($message) {
        $this->message = $message;
    }

    public function resetMessage() {
        $this->message = '';
    }

}

?>
