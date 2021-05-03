<?php

    namespace App\Helpers\Helper;

    class Helper{
        public static function concatForUrls(array $array){
            $url = '';
            foreach ($array as $k => $value){
                $url .= preg_replace('@\/$@' , '' , $value) .'/';
            }
            return trim('/' , $url);
        }
    }
