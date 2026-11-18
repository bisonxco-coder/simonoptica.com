<?php

namespace App\Helpers;


class XmlEscape{
    public static function xmlEscape($content){
        if (!is_string($content)) return "";
        $content = str_replace("\r\n", " ", $content);
        $content = str_replace("\r"  , " ", $content);
        $content = str_replace("\n"  , " ", $content);
        $content = str_replace("\t"  , " ", $content);
        $content = str_replace("&"  , "&amp;", $content);
        $content = str_replace("<"  , "&lt;", $content);
        $content = str_replace(">"  , "&gt;", $content);
        $content = str_replace('"'  , "&quot;", $content);
        return $content;
    }
}
