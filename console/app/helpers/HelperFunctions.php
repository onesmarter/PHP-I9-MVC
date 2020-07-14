<?php

function startSession($sessionName = "user_login") {
    if (!isset($_SESSION) || session_name() != $sessionName) {
        session_name($sessionName);
        ini_set("session.cookie_lifetime","86400");
        ini_set('session.cookie_domain', SESSION_URL);
        session_start();
    }
}

function compare($key1, $key2) {
    return $key1 == $key2 ? 0 : ($key1 > $key2 ? 1 : -1);
}

function randomString($length = 6) {
    $str = "";
    $characters = array_merge(range('A','Z'), range('a','z'), range('0','9'));
    $max = count($characters) - 1;
    for ($i = 0; $i < $length; $i++) {
      $rand = mt_rand(0, $max);
      $str .= $characters[$rand];
    }
    return $str;
}

function getContentBetweenStrings($str, $startSearchString, $endSearchString){
    //$str should contain $startSearchString and $endSearchString
    //the $endSearchString position should be after  $startSearchString end position
    if(empty($str) || empty($startSearchString)  || empty($endSearchString) || ($startPos=strpos($str, $startSearchString))===false || ($endPos=strpos($str, $endSearchString,$startPos+strlen($startSearchString)+1))===false ) {
        return '';
    }
    return substr($str,$startPos+strlen($startSearchString)+1,$endPos-$startPos+strlen($startSearchString));
}

function roundToTheNearest($value, $roundTo, $round) {
    $mod = $value%$roundTo;
    $x= $value+($mod<($roundTo/2)?-$mod:$roundTo-$mod);
    $x = round($x/$round) * $round;
    return $x;
}

function endsWith($string, $endString) { 
    $len = strlen($endString); 
    if ($len == 0) { 
        return true; 
    } 
    if(strlen($string)<$len)
        return false;
    return (substr($string, -$len) === $endString); 
} 

function isUpperCase($char) {
    $upper = strtoupper($char);
    return $upper === $char;
}

function isLowerCase($char) {
    $lower = strtolower($char);
    return $lower === $char;
}