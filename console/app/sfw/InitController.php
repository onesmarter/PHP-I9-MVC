<?php
namespace SFW;
use SFW\Connection;
use SFW\Request;
use RainTPL;
use \SFW\Route;
use \SFW\RouteTypes;

class InitController extends Connection {
    public function __construct(Array $params) {
        parent::__construct();
        if(function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            $headers = [];
        }
        $this->request = new Request([],$_FILES,$headers);
        $this->params = $params;
        $this->tpl   = new RainTPL;
    }

    function init($callingUrl) {
        try {
            $route = Route::findRoute($callingUrl,$this->params,new RouteTypes($_SERVER['REQUEST_METHOD']));
            if($route==null) {
                return $this->tpl->draw('error/404', $return_string = true);
            }
            $loader = new \Composer\Autoload\ClassLoader();
            $controllerPath = $route->controllerPath;
            $controllerPath = $route->getNameSpace().$controllerPath;
            $loader->loadClass($controllerPath);
            $controller = $route->getNameSpace().$route->controller;
            $instance = new $controller();

            $functionName = $route->functionName;
            $params = [];
            if($_SERVER['REQUEST_METHOD']=="POST" || $_SERVER['REQUEST_METHOD']=="PUT") {
                if(isset($_SERVER["CONTENT_TYPE"]) && $_SERVER["CONTENT_TYPE"] == "application/json") {
                    $params = json_decode(file_get_contents("php://input"),true);
                } else {
                    $params = $_POST;
                }
            } else if($_SERVER['REQUEST_METHOD']=="GET") {
                $params = $_GET;
            }

            foreach ($route->params as $p) {
                $params[$p['param']] = $this->params[$p['position']];
            }
            $this->request->data = $params;
            return $instance->$functionName($this->request,$this,$this->tpl);
        } catch (\Throwable $th) {
            if(isInDebugMode()) {
                print_r($th);
            }
            if(IS_FOR_API || (isset($this->request->headers['isJsonOutput']) && $this->request->headers['isJsonOutput']===true)) {
                return json_encode(["status"=>0,"msg"=>"Api Not Found"]);
            }
            return $this->tpl->draw('error/404', $return_string = true);
        }
        
        return 'called';
    }
}