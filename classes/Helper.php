<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Helper
 *
 * @author Daniel
 */
class Helper {

    /**
     * Fixes float input in (Czech languguage): 1 123,56 -> 1234.56
     * It's static, so you can call it easily anywhere like Helper::czFixFloat($whatever)!
     * @param string $fl original float
     * @return string fixed float
     */
    public static function czFixFloat($fl) {
        $fl = str_replace(",", ".", $fl);
        $fl = str_replace(" ", "", $fl);
        return $fl;
    }
}
?>
