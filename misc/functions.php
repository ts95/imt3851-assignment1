<?php

/**
 * This file contains some useful utility functions.
 * ========================================================
 */

/**
 * array_map takes the arguments in the wrong order. This function
 * normalizes it to make it consistent with the filter function.
 * @param array $array
 * @param function $cb
 * @return array
 */
function map($array, $cb) {
    return array_map($cb, $array);
}

/**
 * array_filter preserves the indexes of the array that has been
 * filtered, which is unusual behavior. This method does not.
 * @param array $array
 * @param function $cb
 * @return array
 */
function filter($array, $cb) {
    $elements = [];
    foreach ($array as $element) {
        if ($cb($element)) {
            $elements[] = $element;
        }
    }
    return $elements;
}
