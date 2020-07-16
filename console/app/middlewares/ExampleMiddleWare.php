<?php
namespace SFW\MiddleWares;
use RainTPL;
use SFW\Request;
use SFW\Connection;
class ExampleMiddleWare {
    public function request(Request $request,Connection $connection,RainTPL $tpl) {
        if(!empty($request->data['showError'])) {
            return $tpl->draw('error/404', $return_string = true);
        }
        
    }
}