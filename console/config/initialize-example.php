<?php
    $phpVersions = explode(".",phpversion());
    
    if(intval($phpVersions[0])>7 || (intval($phpVersions[0])==7 && intval($phpVersions[1])>2)) {
        define('PHP_7_4',true);
    } else {
        define('PHP_7_4',false);
    }
    
    if (!isset($_SERVER['REQUEST_SCHEME'])) {
        $_SERVER['REQUEST_SCHEME'] = "http";
    }
    define('ROOT_FOLDER','/RainTplDemo/');
      //Local Host Setup
    define('HOST_PATH',$_SERVER['DOCUMENT_ROOT'].ROOT_FOLDER.'console/');
    define('VIEWS_PATH',HOST_PATH.'view/');
    define('SITE_URL',$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].ROOT_FOLDER.'console/');
    define('SESSION_URL',$_SERVER['SERVER_NAME']);
    define('API_PATH',HOST_PATH.'api/');
    define('API_URL',SITE_URL.'api/');

    //database
    
    define('DB_HOST','localhost');
    define('DB_USER','root');
    define('DB_PASS','');
    define('DB_NAME','');
    define('DB_HOST_PORT','8888');
    
    define('SITE_NAME','');
    

	//the default timezone
	define('DEFAULT_TIMEZONE','UTC');
	//end


	//Mail Driver Details
    define('MAIL_DRIVER','MAILGUN');
	define('MAILGUN_DOMAIN','');
	define('MAILGUN_SECRET','');
    // end
    
    
    try {
        $file=fopen(".env", "r");
        $fileContent = fread($file,filesize(".env"));
        fclose($file);
        $fileContent = explode(";", $fileContent);
        foreach ($fileContent as $content) {
            $params = explode("=",$content);
            if(count($params==2)) {
                if(!defined(trim($params[0]))) {
                    define(trim($params[0]),trim($params[1]));
                }
            }
        }
    } catch (\Throwable $th) {
    }
    if(!defined('PARAMS_SEPERATOR')) {
        define('PARAMS_SEPERATOR','/');
    }

    $consoleFolder = strtolower( str_replace($_SERVER['DOCUMENT_ROOT'],"",HOST_PATH)); 
    $consolePosition = strpos(strtolower($_SERVER['REQUEST_URI']),$consoleFolder."index.php");
    if($consolePosition!==false) {
        $callingUrl = substr($_SERVER['REQUEST_URI'],$consolePosition+strlen($consoleFolder."index.php"));
    } else {
        //NO need to use str_replace here
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

    error_reporting(E_ALL); ini_set('display_errors', '1');
	
	include HOST_PATH."/controller/RainTPL-Controller.php";
	raintpl::configure("base_url", null );
	raintpl::configure("tpl_dir",HOST_PATH."/view/" );
	raintpl::configure("cache_dir",HOST_PATH."/tmp/" );
	raintpl::configure( 'path_replace', false );
	raintpl::configure("htmlspecialchars", true ); 
	//initialize a Rain TPL object
	$tpl = new RainTPL;
	$tpl->assign( "base_url", SITE_URL );
	$tpl->assign( "sub_file_url", VIEWS_PATH );
	$tpl->assign( "console_url", SITE_URL );
	$tpl->assign( "site_name", SITE_NAME );
    $tpl->assign( "portal_name", PORTAL_NAME );

    require HOST_PATH."helpers/HelperFunctions.php";

?>