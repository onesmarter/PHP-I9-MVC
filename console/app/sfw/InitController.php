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
        $isForJsonOutput = IS_FOR_API === true || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'],"application/json") !== false) || (isset($this->request->headers['isJsonOutput']) && $this->request->headers['isJsonOutput']===true);
       
        define('IS_FOR_JSON_OUTPUT',$isForJsonOutput);
    }

    function init($callingUrl) {
        try {
            $route = Route::findRoute($callingUrl,$this->params,new RouteTypes($_SERVER['REQUEST_METHOD']));
            if($route==null) {
                if(IS_FOR_JSON_OUTPUT) {
                    return json_encode(["status"=>0,"msg"=>"Api Not Found"]);
                }
                return $this->tpl->draw('error/404', $return_string = true);
            }
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

            $loader = new \Composer\Autoload\ClassLoader();
            if(!empty($route->middleWare)) {
                try {
                    $loader->loadClass("\SFW\MiddleWares\\".$route->middleWare);
                    $middleWare = "\SFW\MiddleWares\\".$route->middleWare;
                    $middleWare = new $middleWare();
                    $check = $middleWare->request($this->request,$this,$this->tpl);
                    if($check) {
                        return $check;
                    }
                } catch (\Throwable $th) {
                    throw new \Exception($route->middleWare." class is not defined. Please create ".$route->middleWare.".php file in console/app/middlewares folder. Please check console/app/middlewares/ExampleMiddleware.php to know how a middleware class is looks like.", 1);             
                }
            }
            
            $controllerPath = $route->controllerPath;
            $controllerPath = $route->getNameSpace().$controllerPath;
            
            $controller = $route->getNameSpace().$route->controller;
            $instance = new $controller();

            $functionName = $route->functionName;
            
            return $instance->$functionName($this->request,$this,$this->tpl);
        } catch (\Throwable $th) {
            if(isInDebugMode()) {
                print_r($th);
            }
            if(IS_FOR_JSON_OUTPUT) {
                return json_encode(["status"=>0,"msg"=>"Api Not Found"]);
            }
            return $this->tpl->draw('error/404', $return_string = true);
        }
        
        return 'called';
    }
}