<?php

class Util {
    
    public static function isDateValid($str) {
        
        $stamp = strtotime($str);
        if (!is_numeric($stamp)) {
            $stamp = null;
            return false;
        }

        if (checkdate(date('m', $stamp), date('d', $stamp), date('Y', $stamp))) {
            return true;
        }
        return false;
    }
    
}
?>
