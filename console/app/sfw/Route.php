<?php
namespace SFW;

use MyCLabs\Enum\Enum;

/**
 * @author - JINTO PAUL
 * @date   - 04/July/2020
 */

class RouteTypes extends Enum {
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const DELETE = 'DELETE';
    const PATCH = 'PATCH';
}

/**
 * @author - JINTO PAUL
 * @date   - 04/July/2020
 */
class Route implements \JsonSerializable {
    private static $getRoutes = [];
    private static $postRoutes = [];
    private static $putRoutes = [];
    private static $deleteRoutes = [];
    private static $patchRoutes = [];
    private function __construct(String $route, String $controller,RouteTypes $type,$controllerFolderName = null) {
        
        $this->params = [];
        $this->routes = [];
        $this->controller = '';
        $this->functionName = '';
        $this->nameSpace = '';
        $this->position = -1;
        $this->routeType= $type;
        $this->route = $route;
        if($controllerFolderName ==null || empty($controllerFolderName)) {
            $controllerFolderName = 'app/controller/';
        } else if(!endsWith($controllerFolderName,'/')) {
            $controllerFolderName .= '/';
        }
        
    
        //Set controller
        $split = explode("@",$controller);
        if(count($split)!=2) {
            die('Syntax error - '.$controller.' => Please use <b>Controller@functionName</b> syntax');
        }
        if(!file_exists(HOST_PATH.$controllerFolderName.$split[0].'.php')) {
            die('Controller file not found - '.HOST_PATH.$controllerFolderName.$split[0].'.php');
        }
        $this->controllerPath = $split[0];
        $this->functionName = $split[1];

        $controllerSplit = explode("/",$this->controllerPath);
        if(empty($controllerSplit[count($controllerSplit)-1])) {
            die('Controller should not be empty. Controller should not endswith /');
        }
        $this->controller = $controllerSplit[count($controllerSplit)-1];


        //Set routes and params
        $split = explode("/",$route);
        foreach ($split as $value) {
            if(!empty($value)) {
                if(strpos($value,'{')===0 && strpos($value,'}')===strlen($value)-1) {
                    //Set param name to set data to data array
                    $this->params [] = ['position'=>count($this->routes),'param'=>str_replace('{','',str_replace('}','',$value))];
                    //This will helps to identify this position have an dynamic value , eg: {id}
                    $this->routes [] = '';
                } else {
                    //Only set static routes. ie, avoid {id}, {name} ,......
                    $this->routes [] = $value;
                }
                
            }
        }
        

    }

    private function updateRoute() {
        switch ($this->routeType) {
            case RouteTypes::GET():
                Route::$getRoutes[$this->route] = $this;
            break;
            case RouteTypes::PUT():
                Route::$putRoutes[$this->route] = $this;
            break;
            case RouteTypes::DELETE():
                Route::$deleteRoutes[$this->route] = $this;
            break;
            case RouteTypes::PATCH():
                Route::$patchRoutes[$this->route] = $this;
            break;
            case RouteTypes::POST():
                Route::$postRoutes[$this->route] = $this;
            break;
        }
    }

    public function setNameSpace($nameSpace) {
        if($this->position == -1) {
            throw new \Exception("Route not added yet.", 1);        
        }
        if(endsWith($nameSpace,"\\")) {
            $this->nameSpace = $nameSpace;
        } else {
            $this->nameSpace = $nameSpace."\\";
        }
        $this->updateRoute();
        
        return $this;
    }

    

    public function getNameSpace() {
        if(empty($this->nameSpace)) {
            return '\SFW\Controller\\';
        }
        return $this->nameSpace;
    }

    public function jsonSerialize() {
        return ['route'=>$this->route,'params'=>$this->params,'routes'=>$this->routes,'controller'=>$this->controller,'functionName'=>$this->functionName,'nameSpace'=>$this->getNameSpace(),'routeType'=>$this->routeType];
    }

    private static function addRoute(String $route, String $controller,Array &$routes,RouteTypes $type,$controllerFolderName = null) {
        if(empty($route)) {
            die("Route should not be empty for GET - ".$route);
        }
        if(array_key_exists($route,$routes)) {
            die("Route already defined for ".$type." - ".$route);
        }
        $routeModel = new Route($route,$controller,$type,$controllerFolderName);
        $routeModel->position = count($routes);
        $routes [$route] = $routeModel;
        return $routeModel;
    }

    public static function get(String $route, String $controller,$controllerFolderName = null) {
        return Route::addRoute($route,$controller,ROUTE::$getRoutes,RouteTypes::GET(),$controllerFolderName);
    } 

    public static function post(String $route, String $controller,$controllerFolderName = null) {
        return Route::addRoute($route,$controller,ROUTE::$postRoutes,RouteTypes::POST(),$controllerFolderName);
    } 

    public static function delete(String $route, String $controller,$controllerFolderName = null) {
        return Route::addRoute($route,$controller,ROUTE::$deleteRoutes,RouteTypes::DELETE(),$controllerFolderName);
    } 

    public static function put(String $route, String $controller,$controllerFolderName = null) {
        return Route::addRoute($route,$controller,ROUTE::$putRoutes,RouteTypes::PUT(),$controllerFolderName);
    } 

    public static function patch(String $route, String $controller,$controllerFolderName = null) {
        return Route::addRoute($route,$controller,ROUTE::$patchRoutes,RouteTypes::PATCH(),$controllerFolderName);
    } 

    public static function findRoute(String $url,Array $params,RouteTypes $callType) {
        if(empty($url)) {
            $url = "/";
        }
        switch ($callType) {
            case RouteTypes::GET():
                return Route::getRoute($url,$params,Route::$getRoutes);
            case RouteTypes::PUT():
                return Route::getRoute($url,$params,Route::$putRoutes);
            case RouteTypes::DELETE():
                return Route::getRoute($url,$params,Route::$deleteRoutes);
            case RouteTypes::PATCH():
                return Route::getRoute($url,$params,Route::$patchRoutes);
            case RouteTypes::POST():
                return Route::$postRoutes[$url];
        }
        return null;
    }

    private static function getRoute(String $url,Array $params,Array &$routes) {
        $count = isset($params) && $params != null ? count($params):0;
        foreach ($routes as $key => $value) {
            if($key==$url) {
                return $value;
            }
            if($count==count($value->routes)) {
                $found = true;
                for ($i=0; $i < $count; ++$i) { 
                    if(!empty($value->routes[$i]) && $value->routes[$i] != $params[$i]) {
                        $found = false;
                    break;
                    }
                }
                if($found === true) {
                    return $value;
                }
            }
        }
    }
}