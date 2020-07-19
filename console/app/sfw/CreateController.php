<?php
namespace SFW;
use SFW\Connection;
use SFW\Request;


class CreateController {

    

    private function createModelData($modelName,$tableName) {
        return "<?php
namespace SFW\\Models;
use SFW\Model;
        
class ".$modelName."Model extends Model {
    protected \$table = '$tableName';
}";
    }

    private function createControllerData($controllerName) {
        return "<?php

namespace SFW\Controller;
use SFW\Connection;
use SFW\Request;
use RainTPL;


class ".$controllerName."Controller {
    public function example(Request \$request,Connection \$connection,RainTPL \$tpl) {
        return \$tpl->draw('sample', \$return_string = true);
    }
}";
    }

    private function createMiddelWareData($middleWareName) {
        return "<?php
namespace SFW\MiddleWares;
use RainTPL;
use SFW\Request;
use SFW\Connection;
class ".$middleWareName."MiddleWare {
    public function request(Request \$request,Connection \$connection,RainTPL \$tpl) {
        //if(exitCall) {
        //    return 'Exit message';
        //}
    }
}";
    }

    private function checkModelName($modelName,$type = "Model") {
        if(empty($modelName)) {
            throw new \Exception($type." name should not be empty.<br>eg. Use 'User' for 'User".$type."'.", 1);      
        }
        // if(isLowerCase($modelName[0])) {
        //     $modelName = strtoupper($modelName[0]).substr($modelName,1);
        // }
        $modelName = ModelHelper::createName($modelName,true);
        $avoidEndStrings = ['php','.php','models','model','controller','controllers','middleware','middlewares'];
        $modelNameLower = strtolower($modelName);
        foreach ($avoidEndStrings as $avoid) {
            if(endsWith($modelNameLower,$avoid)) {
                $modelNameLower = substr($modelNameLower,0,strlen($modelNameLower)-strlen($avoid));
                $modelName = substr($modelName,0,strlen($modelName)-strlen($avoid));
            }
        }
        if(empty($modelName)) {
            throw new \Exception($type." name should not be end with ".implode(', ',$avoidEndStrings).".<br>eg. Use 'User' for 'User".$type."'.", 1);      
        }
        if(isUpperCase($modelName[strlen($modelName)-1])) {
            $modelName = substr($modelName,0,strlen($modelName)-1).strtolower($modelName[strlen($modelName)-1]);
        }
        return $modelName;
    }

    private function writeData($content,$fileName) {
        try {
            if(!file_exists($fileName)) {
                $file = \fopen($fileName,'wb');
                \fwrite($file,$content);
                fclose($file);
                return false;
            } else {
                return '<b>'.$fileName.'</b> already exists.';         
            }
        } catch (\Throwable $th) {
            return 'Can\'t create <b>'.$fileName.'</b> file. ('.$th->getMessage().")";  
        }
    }
 
    public function createModel(Request $request,Connection $connection) {
        if(isInProductionMode()) {
            return "Can't create a model in <b>production mode</b>";
        }
        
        $modelName = $this->checkModelName($request->data['model']);    
        $tableName = isset($request->data['table'])?$request->data['table']:Model::createTableName($modelName);
        if($response = $this->writeData($this->createModelData($modelName,$tableName),HOST_PATH.'app/models/'.$modelName.'Model.php') ) {
            return $response;
        }
        $create = "CREATE TABLE IF NOT EXISTS ".$tableName."(id INT(11) PRIMARY KEY AUTO_INCREMENT,created_at DATETIME NOT NULL default CURRENT_TIMESTAMP,updated_at DATETIME NOT NULL default CURRENT_TIMESTAMP)";
        $connection->query($create);
        return "<b>Created.</b> Model Name = <b>".$modelName.'Model.php</b>'."  Table Name = <b>".$tableName."</b>";
    }

    public function createController(Request $request) {
        if(isInProductionMode()) {
            return "Can't create controller in <b>production mode</b>";
        }
        $controllerName = $this->checkModelName($request->data['controller']); 
        if($response = $this->writeData($this->createControllerData($controllerName),HOST_PATH.'app/controller/'.$controllerName.'Controller.php') ) {
            return $response;
        }
        return "<b>Created.</b> Controller Name = <b>".$controllerName.'Controller.php</b>';
    }

    public function createMiddleWare(Request $request) {
        if(isInProductionMode()) {
            return "Can't create middleware in <b>production mode</b>";
        }
        $middleWareName = $this->checkModelName($request->data['middleware']); 
        if($response = $this->writeData($this->createMiddelWareData($middleWareName),HOST_PATH.'app/middlewares/'.$middleWareName.'MiddleWare.php') ) {
            return $response;
        }
        return "<b>Created.</b> MiddleWare Name = <b>".$middleWareName.'MiddleWare.php</b>';
    }
    
}