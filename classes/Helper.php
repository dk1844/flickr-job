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

    /**
     * Parses date in form of 1.12.2010 from a string, return today's date on error.
     * @param integer unix datetime stamp
     */
    public static function czStrToUnixDate($string){
        $full_date_pattern = "/^(\d{1,2})\.(\d{1,2})\.(\d{4,4})$/";
        if (preg_match($full_date_pattern, $string) == 0)
            return date("U");

    //went fine, parse date

        list($day, $month, $year) = explode(".", $string);
        return date("U", mktime(0, 0, 0, $month, $day, $year));
     }

     /**
     * Parses date in form of 1.12.2010 from a string, return today's date on error.
     * @param integer unix datetime stamp
     */
    public static function czUnixDateToStr($unix_stamp){
            if ($unix_stamp == 0)
                return date("j.n.Y");
            return date("j.n.Y", $unix_stamp);
        }
}
?>
