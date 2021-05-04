<?php

/**
 * Helper file, autoloaded in all routes.
 *
 * Please write codes like this.
 * Thanks, for all.
 *
 * PHP version 7.2
 *
*/
if (!function_exists('concatForUrls')) {
    function concatForUrls(array $array)
    {
        $url = '';
        foreach ($array as $k => $value) {
            $url .= preg_replace('@\/$@', '', $value) . '/';
        }
        return trim($url , '/');
    }
}
