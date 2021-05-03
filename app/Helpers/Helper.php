<?php

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
