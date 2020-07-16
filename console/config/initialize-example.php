<?php
    /************************ ROOT CONFIGS ********************************************/
    define('PARAMS_SEPERATOR','/');
    define('MODE',"debug");//development , production , debug
    $phpVersions = explode(".",phpversion());
    
    if(intval($phpVersions[0])>7 || (intval($phpVersions[0])==7 && intval($phpVersions[1])>2)) {
        define('PHP_7_4',true);
    } else {
        define('PHP_7_4',false);
    }
    
    if (!isset($_SERVER['REQUEST_SCHEME'])) {
        $_SERVER['REQUEST_SCHEME'] = "http";
    }
    /************************ PATHS ********************************************/
    define('SITE_NAME','');
    define('ROOT_FOLDER','/RainTplDemo/');
    define('HOST_PATH',$_SERVER['DOCUMENT_ROOT'].ROOT_FOLDER.'console/');
    define('VIEWS_PATH',HOST_PATH.'view/');
    define('SITE_URL',$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].ROOT_FOLDER.'console/');
    define('SESSION_URL',$_SERVER['SERVER_NAME']);
    define('API_PATH',HOST_PATH.'api/');
    define('API_URL',SITE_URL.'api/');

    /************************ RESOURCE PATHS ********************************************/
    define('RESOURCE_PATH',HOST_PATH.'resources/');
    define('CSS_PATH',RESOURCE_PATH.'css/');
    define('JS_PATH',RESOURCE_PATH.'js/');
    define('IMAGES_PATH',RESOURCE_PATH.'images/');
    define('FONTS_PATH',RESOURCE_PATH.'fonts/');
    define('PACKAGES_PATH',RESOURCE_PATH.'packages/');
    define('ASSETS_PATH',RESOURCE_PATH.'assets/');

    /************************** DB *********************************************/
    
    define('DB_HOST','localhost');
    define('DB_USER','root');
    define('DB_PASS','root');
    define('DB_NAME','test');
    define('DB_HOST_PORT','8888');
    
    
	/************************** DATE TIME *********************************************/
	define('DEFAULT_TIMEZONE','UTC');
	//end


	/************************** MAIL *********************************************/
    define('MAIL_DRIVER','MAILGUN');
	define('MAILGUN_DOMAIN','');
	define('MAILGUN_SECRET','');
    
    

    $consoleFolder = strtolower( str_replace($_SERVER['DOCUMENT_ROOT'],"",HOST_PATH)); 
    $consolePosition = strpos(strtolower($_SERVER['REQUEST_URI']),$consoleFolder."index.php");
    if($consolePosition!==false) {
        $callingUrl = substr($_SERVER['REQUEST_URI'],$consolePosition+strlen($consoleFolder."index.php"));
    } else {
        //NO need to use replace here
        $callingUrl = str_replace(ROOT_FOLDER.$consoleFolder."index.php","",$_SERVER['REQUEST_URI']);
    }
    
    $consolePosition = strpos(strtolower($callingUrl),$consoleFolder);
    if($consolePosition!==false) {
        $callingUrl = substr($callingUrl,$consolePosition+strlen($consoleFolder));
    }
    // $_params = str_replace(ROOT_FOLDER.$consoleFolder,"",$_params);
    $apiFolder = strtolower( str_replace(HOST_PATH,"",API_PATH));
    $apiPosition = strpos(strtolower($callingUrl),$apiFolder);
    

    define('IS_FOR_API',$apiPosition===0?true:false);


    if(IS_FOR_API===true) {
        $callingUrl = substr($callingUrl,strlen($apiFolder));
    }
    
    $_params = explode("/", $callingUrl);
    $allParams =[];
    $callingModule = "";
    for ($i=0; $i < count($_params) ; ++$i) { 
        if(!empty(trim($_params[$i]))) {
            $allParams [] = $_params[$i];
        }
    }

    function isInProductionMode() {
        return MODE == "production";
    }

    function isInDebugMode() {
        return MODE == "debug";
    }

    include 'config.php';

?>