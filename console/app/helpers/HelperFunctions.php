<?php

function startSession($sessionName = "anonymous") {
    if (session_status() == PHP_SESSION_NONE) {
        if($sessionName != "user") {
            startSession("user");
            if(empty($_SESSION['user'])) {
                session_destroy();
            } else {
                return;
            }
        }
        session_name($sessionName);
        ini_set("session.cookie_lifetime","86400");
        ini_set('session.cookie_domain', SESSION_URL);
        session_start();    
    }
}
function destroySession() {
    if (session_status() != PHP_SESSION_NONE) {
        unset($_SESSION);
        session_destroy();
    }
}

function jsonResponse($data,$status=1,$msg="") {
    return json_encode(['status'=>$status,'msg'=>$msg,'data'=>$data]);
}


/**
 * $tpl -> RainTPL instance
 * $data -> Sequence array or a single object will be assigned with the key "data"
 *          In associative array all elements will be assigned seperately
 *              eg: ['models'=>$models,'msg'=>$message] will assigned with two keys 'models' and 'msg'
 * $htmlFile -> Html file to be render
 */
function htmlResponse($tpl,$data,String $htmlFile) {
    if($data===null || !is_array($data) || count($data)==0) {
        $tpl->assign( "data", $data);
    } else if(array_keys($data) !== range(0, count($data) - 1)) {
        foreach ($data as $key => $value) {
            $tpl->assign( $key, $value);
        }
    }
    return $tpl->draw($htmlFile, $return_string = true);
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