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
    const GEO_UI_PRECISION = 4;

    private $page;
    private $search;
    private $rerank;

    //to be defined somewhere else or loaded from ..?
    const PAGE_TPL_LOCATION = "./tpl/page.tpl.xhtml";
    private $pageTpl;
    const INPUT_TPL_LOCATION = "./tpl/inputBlock.tpl.xhtml";
    private $inputTpl;

    public function __construct(Search $s, Rerank $rr) {
        $this->search = $s;
        $this->rerank = $rr;

        $this->loadTemplates();
    }

    public function loadTemplates() {
        $this->pageTpl = file_get_contents(self::PAGE_TPL_LOCATION);
        $this->inputTpl = file_get_contents(self::INPUT_TPL_LOCATION);
    }

    public function buildUI() {
        $inputBlock = $this->inputTpl;
        $inputBlock = str_replace("{inputText}", htmlspecialchars($_REQUEST["input"]), $inputBlock);
        $inputBlock = str_replace("{searchTypeSelector}", $this->createSearchTypeSelector($_REQUEST["searchType"]), $inputBlock);

        $inputBlock = str_replace("{rerank_selected}", (isset($_REQUEST["rerank"]) ? "checked" : ""), $inputBlock);



        if (isset($_REQUEST["rerank"])) {
            $geo_lat = htmlspecialchars($_REQUEST["geo_lat"]);
            $geo_long = htmlspecialchars($_REQUEST["geo_long"]);

            $title_similarity_pattern = htmlspecialchars($_REQUEST["title_similarity_pattern"]);

            foreach (Rerank::$types as $value) {
                $out = "";
                if ($this->rerank->getType() == $value)
                    $out = "checked=\"checked\"";
                //e.g. {geo_rrTypeChecked}
                $inputBlock = str_replace("{{$value}_rrTypeChecked}", $out, $inputBlock);
            }
        } else {
            $geo_lat = "";
            $geo_long = "";
            $$title_similarity_pattern = "";
        }
        $inputBlock = str_replace("{geo_lat}", $geo_lat, $inputBlock);
        $inputBlock = str_replace("{geo_long}", $geo_long, $inputBlock);

        $inputBlock = str_replace("{title_similarity_pattern}", $title_similarity_pattern, $inputBlock);

        $page = $this->pageTpl;
        $page = str_replace("{inputBlock}", $inputBlock, $page); //as seen above :)
        $page = str_replace("{message}", $this->search->getMessage(), $page);


        if ($this->search->isCommitted()) {
            //generate output
            $output_imgs = $this->createAImgsTable($this->search->getResultPhotos());
            $heading = "<h1>Here it is:</h1>\n";
            $output = $heading . $output_imgs;
        } else {
            $output = "<p>Start the search!</p>";
        }
        $page = str_replace("{outputBlock}", $output, $page);

        $this->page = $page;
    }

    public function createAImg(Photo $p) {
        $out = "";
        $out = "<h4>" . $p->getTitle() . "</h4>";

        $img = "<img src=\"" . $p->getThumbnailUrl() . "\" alt=\"" . htmlspecialchars($p->getTitle()) . "\"  />";
        $out .= "<a href=\"" . $p->getFullsizeUrl() . "\" >" . $img . "</a><br/>\n";

        $out .= "Views: <span class=\"help\" title=\"Because we cache...\">&#177</span>" . $p->getViews() . "<br/>\n";

        /*
          if ($p->getGeo()->isValid() && $this->rerank->getLocal_geo()->isValid()) { //both local & picture geo valid
          $lat = $p->getGeo()->getLatitude();
          $long = $p->getGeo()->getLongitude();

          $out .= "geo={lat=$lat;long=$long}<br />";
          $out .= "Distance = " . $p->getRrDistance() . "km";
          }
         */

        if ($p->getGeo()->isValid()) {
            $lat = round($p->getGeo()->getLatitude(), UI::GEO_UI_PRECISION);
            $long = round($p->getGeo()->getLongitude(), UI::GEO_UI_PRECISION);
            $out .= "geo={lat=$lat;long=$long}<br />";

            if ($this->rerank->getLocal_geo()->isValid()) {
                $out .= "Distance = " . round($p->getRrDistance(),UI::GEO_UI_PRECISION) . "km";
            }
        }

        if ($this->rerank->getType() == "title_similarity") {
            $out .= "title_similarity = " . $p->getTitleSimilarity() . "<br/>\n";
        }

        return $out;
    }

    public function createAImgsTable($array) {
        $out = "";
        $table_w = 3;

        if (empty($array))
            return "<p> Search result empty, try different keywords or so.. </p>";


        $out .= "<table style=\"border:1px dotted maroon;\"><tr>";

        for ($index = 0; $index < count($array); $index++) {
            $one = $array[$index];

            if ($index % $table_w == 0 && $index != 0 && ($index) != count($array)) { //nasobek, neni 0. ani posledni
                $out .= "</tr>\n<tr>";
            }

            $out .= "<td>";

            $out .= $this->createAImg($one) . "\n";
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

    public function getPage() {
        return $this->page;
    }

    public function setPage($page) {
        $this->page = $page;
    }

    public function getSearch() {
        return $this->search;
    }

    public function setSearch($search) {
        $this->search = $search;
    }

    public function getRerank() {
        return $this->rerank;
    }

    public function setRerank($rerank) {
        $this->rerank = $rerank;
    }

}

?>
