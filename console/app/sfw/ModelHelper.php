<?php
namespace SFW;

use SFW\Connection;
use SFW\Helpers\QueryBuilder;

class ModelHelper {
    public function __construct($table,$primaryKey,$tableKeys)  {
        $this->primaryColumn = $primaryKey;
        $this->table = $table;
        $this->primaryVariable = static::createName($primaryKey);
        foreach ($tableKeys as $key ) {
            $var = static::createName($key);
            $this->tblKeys [$key] = $var;
            $this->varKeys[$var] = $key;
        }
        $this->varCount = count($tableKeys);
    }

    public static function initalizeModel(String $modelClassName,?Connection $controller) {
        $loader = new \Composer\Autoload\ClassLoader();
        $loader->loadClass($modelClassName);
        return new $modelClassName($controller);
    }

    public static function createName($str,bool $isForClass = false): String {
        $value = '';
        $prevIsUpper = false;
        $nextIsUpper = $isForClass;
        for ($i=0,$j=strlen($str); $i < $j ; ++$i) {
            $char = $str[$i]; 
            if($char == '_') {
                $nextIsUpper = $prevIsUpper === false && !empty($value);
            } else if($nextIsUpper===true && $prevIsUpper !== true) {
                $prevIsUpper = true;
                $nextIsUpper = false;
                $value .= strtoupper($char);
            } else if($prevIsUpper === true || empty($value)) {
                $prevIsUpper = false;
                $value .= strtolower($char);
            } else {
                $prevIsUpper = isUpperCase($char);
                $value .= $char;
            }
        }
        return $value;
    }

    public function getModelValues(Model &$model,bool $forDatabase = false,Array $varNames = null): Array {
        $values = [];
        foreach ($this->varKeys as $key => $value) {
            if($varNames!==null && !array_key_exists($key,$varNames)) {
                continue;
            }
            if(isset($model->$key)) {
                $values[$forDatabase === false? $key : $value] = $model->$key;
            } else {
                $values [$forDatabase === false? $key : $value] = NULL;
            }
        }
        return $values;
    }

    public function getFieldsHaveValue(Model &$model,bool $forDatabase = false,Array $varNames = null): Array {
        $values = [];
        foreach ($this->varKeys as $key => $value) {
            if(isset($model->$key) && ($varNames===null || array_key_exists($key,$varNames))) {
                $values[$forDatabase === false? $key : $value] = $model->$key;
            }
        }
        return $values;
    }

    public function arrayToModel(Array $array,Model &$model,bool $fromDatabase = false) {
        if(!empty($array)) {
            $keyArray = $fromDatabase===true? $this->tblKeys: $this->varKeys;
           
            foreach ($array as $key => $value) {
                if(isset($keyArray[$key])) {
                    $var = $keyArray[$key];
                    $model->$var = $value;
                }
            }         
        }
        return $model;
    }



    public function save(Model &$model,Connection $connection): Array  {
        $primaryKey = $this->primaryVariable;
        if(empty($model->$primaryKey)) {
            return $this->insert($model,$connection);
        }
        return $this->update($model,$connection);
    }



    public function update(Model &$model,Connection $connection): Array {
        
        $query = new QueryBuilder($connection);
        $primaryKey = $this->primaryVariable;
        if(empty($model->$primaryKey)) {
            if(isInDebugMode()) {
                throw new \Exception("Can't update ".get_class($model).". Primary key value is empty. <br>".json_encode($this->getModelValues($model)), 1);     
            }
            return  ['type'=>'update','count'=>0];
        }
        $data = $this->getFieldsHaveValue($model,true);
        unset($data[$this->primaryColumn]);
        if(count($data)==0) {
            if(isInDebugMode()) {
                throw new \Exception("Can't update ".get_class($model).". Set value to atleast one column. <br>".json_encode($this->getModelValues($model)), 1);     
            }
            return  ['type'=>'update','count'=>0];
        }
        $query->where($this->primaryColumn,$model->$primaryKey);
        $sql =  $query->updateQuery($this->table,$data,"");
        $count = $connection->update($sql);
        return  ['type'=>'update','count'=>$count];
    }

    public function insert(Model &$model,Connection $connection): Array {
        $sql =  $connection->insertQuery($this->table,$this->getFieldsHaveValue($model,true),"");
        $id = $connection->insert($sql);
        return  ['type'=>'insert','id'=>$id];
    }

    public function insertOrUpdate(Model &$model,Connection $connection): Array {
        $primaryKey = $this->primaryVariable;
        if(empty($model->$primaryKey)) {
            return $this->insert($model,$connection);
        }
        $query = new QueryBuilder($connection);
        $columns = $this->getFieldsHaveValue($model,true);
        $updateColumns = $columns;
        unset($updateColumns[$this->primaryColumn]);

        $sql =  $query->insertOrUpdateQuery($this->table,$columns,$updateColumns,true);
        $result = $connection->query($sql);
        if($result) {
            return  ['type'=>'insertOrUpdate','id'=>$connection->insertId(),'count'=>$connection->affectedRows()];
        }
        return  ['type'=>'insertOrUpdate','id'=>-1,'count'=>0];
    }

    public function delete(Model &$model,Connection $connection): Array {
        $primaryKey = $this->primaryVariable;
        if(empty($model->$primaryKey)) {
            if(isInDebugMode()) {
                throw new \Exception("Can't delete ".get_class($model).". Primary key value is empty. <br>".json_encode($this->getModelValues($model)), 1);     
            }
            return  ['type'=>'delete','count'=>0];
        }
        $query = new QueryBuilder($connection);
        $query->where($this->primaryColumn,$model->$primaryKey);
        $sql =  $query->deleteQuery($this->table);
        $count = $connection->delete($sql);
        return  ['type'=>'delete','count'=>$count];
    }

    public static function find(String $modelClassName,$primaryValue,?Connection $connection = NULL,Array $columns = array()) {
        if($connection === null) {
            $connection = new Connection();
        }
        $model = static::initalizeModel($modelClassName,$connection);
        if(empty($primaryValue)) {
            if(isInDebugMode()) {
                throw new \Exception("Can't fetch data ".get_class($model).". Primary key value is empty.");     
            }
            return null;
        }
        return static::findOne($modelClassName,$connection,[$model->modelHelper->primaryColumn=>$primaryValue],null,true,$columns,$model);
    }

    public static function findOne(String $modelClassName,?Connection $connection = NULL,Array $conditionArray = array(),String $orderBy = NULL,bool $ascending = true,Array $columns = array(),$model=NULL) {
        if($connection === null) {
            $connection = new Connection();
        }
        if($model===null) {
            $model = static::initalizeModel($modelClassName,$connection);
        }
        $query = new QueryBuilder($connection);
        foreach ($conditionArray as $key => $value) {
            $query->where($key,$value);
        }
        if($orderBy!==null) {
            $query->orderBy($orderBy,$ascending);
        } else if(array_key_exists($model->modelHelper->primaryColumn,$columns)) {
            $query->orderBy($model->modelHelper->primaryColumn,$ascending);
        }  
        
        $sql = $query->getQuery($model->modelHelper->table,$columns);
        return static::findOneByQuery($modelClassName,$sql,$connection,$model);
    }

    public static function findOneByQuery(String $modelClassName,String $sqlQuery,?Connection $connection = NULL,$model=NULL) {
        if($connection === null) {
            $connection = new Connection();
        }
        if($model===null) {
            $model = static::initalizeModel($modelClassName,$connection);
        }
        $data = $connection->getSingleByQuery($sqlQuery,true);
        if($data === false) {
            return false;
        }
        return $model->modelHelper->arrayToModel($data,$model,true);
    }

    public static function findAll($modelClassName,?Connection $connection = NULL,Array $conditionArray = array(),Array $columns = array(),String $orderBy = NULL,bool $ascending = true,int $limit=0,int $offset=-1) {
        if($connection === null) {
            $connection = new Connection();
        }
        $model = static::initalizeModel($modelClassName,null);
        $query = new QueryBuilder($connection);
        foreach ($conditionArray as $key => $value) {
            $query->where($key,$value);
        }
        if($orderBy!==null) {
            $query->orderBy($orderBy,$ascending);
        } else if(array_key_exists($model->modelHelper->primaryColumn,$columns)) {
            $query->orderBy($model->modelHelper->primaryColumn,$ascending);
        }  
        $query->limit($limit,$offset);
        
        $sql = $query->getQuery($model->modelHelper->table,$columns);
        return static::findAllByQuery($modelClassName,$sql,$connection);
    }

    public static function findAllByQuery($modelClassName,String $sqlQuery,?Connection $connection = NULL,$dummyModel = NULL) {
        if($connection === null) {
            $connection = new Connection();
        }
        if($dummyModel === null) {
            $dummyModel = static::initalizeModel($modelClassName,null);
        }
        $result = $connection->query($sqlQuery);
        $models = [];
        $dummyModel->modelHelper->initalizeModel($modelClassName,null);
        while($row = $connection->fetchAssoc($result,false,false)) {
            $model = new $modelClassName($connection);
            $models [] = $model->modelHelper->arrayToModel($row,$model,true);
        }
        $connection->freeResult($result);
    
        return $models;
    }

    public static function pagination($modelClassName,?Connection $connection = NULL,Array $conditionArray = array(),Array $columns = array(),String $orderBy = NULL,bool $ascending = true,int $limit=25,int $offset=0) {
        if($connection === null) {
            $connection = new Connection();
        }
        $model = static::initalizeModel($modelClassName,null);
        $query = new QueryBuilder($connection);
        foreach ($conditionArray as $key => $value) {
            $query->where($key,$value);
        }
        if($orderBy!==null) {
            $query->orderBy($orderBy,$ascending);
        } else if(array_key_exists($model->modelHelper->primaryColumn,$columns)) {
            $query->orderBy($model->modelHelper->primaryColumn,$ascending);
        }  
        $sql = $query->getQuery($model->modelHelper->table,$columns);
        $result = $connection->query($sql);
        $totalCount = $connection->totalRows($result);
        $query->limit($limit,$offset);
        
        $sql = $query->getQuery($model->modelHelper->table,$columns,$model);
        $models =  static::findAllByQuery($modelClassName,$sql,$connection);
        $limit = $limit===null || !is_numeric($limit) || $limit<1?1:$limit;
        $page = intval($offset/$limit)+1;
        $totalPages = intval($totalCount/$limit) + (intval($totalCount%$limit)>0?1:0);
        $count = $models?count($models):0;
        return ['totalCount'=>$totalCount,'currentPage'=>$page,'totalPages'=>$totalPages,'from'=>$offset,'requestedCount'=>$limit,'count'=>$count,'data'=>$models];
    }
    
}