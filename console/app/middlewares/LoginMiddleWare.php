<?php
namespace SFW\MiddleWares;
use RainTPL;
use SFW\Request;
use SFW\Connection;
class LoginMiddleWare {
    public function request(Request $request,Connection $connection,RainTPL $tpl) {
        if(!empty($_SESSION['user'])) {
            if(IS_FOR_JSON_OUTPUT===true) {
                return jsonResponse(null,0,"Logged users cannot access this section");
            }
            header("location:verify");
            exit;
        }
        
    }
}