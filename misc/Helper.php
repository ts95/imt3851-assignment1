<?php

class Helper {
    /**
     * Prints a formatted array to the response.
     * Used for debugging.
     * @param array $array
     */
    public static function dd($array) {
        echo '<pre>';
        print_r($array);
        echo '</pre>';
    }

    /**
     * Prints text to the console & the response.
     * @param string $text
     */
    public static function p($text) {
        $str = (string) $text;
        error_log($str);
        echo "<p>$str</p>";
    }
}
