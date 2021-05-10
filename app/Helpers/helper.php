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

if(!function_exists('uniqueid')){
    function getUnique($size = 5){
        return substr( uniqid(), -$size);
    }
}

if(!function_exists('arraySearch')){
    function arraySearch($search , $array , $column){

        if(!isAssoc($array)) dd($array);//.. throw new \Exception('Array will not be Associante');

        return array_search( $search, array_column($array , $column));
    }
}


if(!function_exists('isAssoc')){
    function isAssoc(array $array){
        return array_values($array) === $array;
    }
}

if(!function_exists('slugify')){
    function slugify(string $str){
        $slug = \Str::slug($str) . '-' . getUnique();
        return $slug  . getUnique(100 - strlen($slug));
    }
}

