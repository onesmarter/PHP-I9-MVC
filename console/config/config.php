<?php 
	error_reporting(E_ALL); ini_set('display_errors', isInProductionMode()?'0':'1');
	
	include HOST_PATH."app/sfw/RainTPL-Controller.php";
	raintpl::configure("base_url", SITE_URL );
	raintpl::configure("tpl_dir",VIEWS_PATH );
	raintpl::configure("cache_dir",HOST_PATH."tmp/" );
	raintpl::configure( 'path_replace', false );
	raintpl::configure("htmlspecialchars", true ); 
	//initialize a Rain TPL object
	$tpl = new RainTPL;
	$tpl->assign( "base_url", SITE_URL );
	$tpl->assign( "sub_file_url", VIEWS_PATH );
	$tpl->assign( "console_url", SITE_URL );
	$tpl->assign( "site_name", SITE_NAME );
	$tpl->assign( "portal_name", PORTAL_NAME );
	
	

	require HOST_PATH."app/helpers/HelperFunctions.php";
	