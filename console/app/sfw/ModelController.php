<?php
namespace SFW;
use SFW\Connection;
use SFW\Models\yyy\HaiModel;
use SFW\Models\JintoModel;
use SFW\Request;
use RainTPL;


class ModelController {

    

    private function createModelData($modelName,$tableName) {
        return "<?php
namespace SFW\\Models;
use SFW\Model;
        
class ".$modelName."Model extends Model {
    protected \$table = '$tableName';
}";
    }

    private function checkModelName($modelName) {
        if(empty($modelName)) {
            throw new \Exception("Model name should not be empty.<br>eg. Use 'User' for 'UserModel'.", 1);      
        }
        if(isLowerCase($modelName[0])) {
            $modelName = strtoupper($modelName[0]).substr($modelName,1);
        }
        $avoidEndStrings = ['php','.php','models','model'];
        $modelNameLower = strtolower($modelName);
        foreach ($avoidEndStrings as $avoid) {
            if(endsWith($modelNameLower,$avoid)) {
                $modelNameLower = substr($modelNameLower,0,strlen($modelNameLower)-strlen($avoid));
                $modelName = substr($modelName,0,strlen($modelName)-strlen($avoid));
            }
        }
        if(isUpperCase($modelName[strlen($modelName)-1])) {
            $modelName = substr($modelName,0,strlen($modelName)-1).strtolower($modelName[strlen($modelName)-1]);
        }
        return $modelName;
    }
 
    public function create(Request $request,Connection $connection,RainTPL $tpl) {

        

        if(isInProductionMode()) {
            return "Can't create a model in <b>production mode</b>";
        }
        
        $modelName = $this->checkModelName($request->data['model']);    
        $tableName = isset($request->data['table'])?$request->data['table']:Model::createTableName($modelName);
        
        if(!file_exists(HOST_PATH.'app/models/'.$modelName.'Model.php')) {
            $file = \fopen(HOST_PATH.'app/models/'.$modelName.'Model.php','wb');
            \fwrite($file,$this->createModelData($modelName,$tableName));
            fclose($file);
        } else {
            return '<b>'.$modelName.'Model.php</b> already exists.';         
        }
        $create = "CREATE TABLE IF NOT EXISTS ".$tableName."(id INT(11) PRIMARY KEY AUTO_INCREMENT,created_at DATETIME NOT NULL default CURRENT_TIMESTAMP,updated_at DATETIME NOT NULL default CURRENT_TIMESTAMP)";
        $connection->query($create);
        return "<b>Created.</b> Model Name = <b>".$modelName.'Model.php</b>'."  Table Name = <b>".$tableName."</b>";
    }

    public function update(Request $request,Connection $connection,RainTPL $tpl) {
        $result = $connection->query("SELECT * FROM `INFORMATION_SCHEMA`.`TABLES` WHERE TABLE_NAME LIKE  '".$connection->sanitize($request->data['table'])."'");//'DESCRIBE '.$connection->sanitize($request->data['table']));
        if($connection->totalRows($result)==0) {
            throw new \Exception("Table ".$request->data['table']." not found.", 1);
            
        }
        while($row = $connection->fetchAssoc($result,false,false)) {
            echo json_encode($row);
            // echo "{$row['Field']} - {$row['Type']}n";
        }
    }

    
}