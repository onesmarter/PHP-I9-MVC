<?php
namespace SFW;

use Closure;
use SFW\Connection;
use SFW\Helpers\QueryBuilder;

class ModelHelper {
    public function __construct($table,$primaryKey,$tableKeys)  {
        $this->primaryColumn = $primaryKey;
        $this->table = $table;
        $this->primaryVariable = static::createName($primaryKey);
        $this->tableKeys = $tableKeys;
        $this->newVariables = [];
        foreach ($tableKeys as $key ) {
            $var = static::createName($key);
            $this->tblKeys [$key] = $var;
            $this->varKeys[$var] = $key;
            $this->variableKeys[] = $var;
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

    public function isVarExits($variableName) {
        return in_array($variableName,$this->variableKeys);
    }

    public function addNewVariable($variableName) {
        if(!empty($variableName) && !$this->isVarExits($variableName) && !in_array($variableName,$this->newVariables)) {
            $this->newVariables[]=$variableName;
        }
    }

    public function removeNewVariable($variableName) {
        echo array_search($variableName,$this->newVariables);
        if(!empty($variableName)) {
            $newArray = [];
            foreach ($this->newVariables as $key) {
                if($variableName!=$key) {
                    $newArray[]=$key;
                } else {
                    echo $variableName." ".$key."<br>";
                }
            }
            $this->newVariables = $newArray;
        }
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
        if($forDatabase===false) {
            foreach ($this->newVariables as $key) {
                if($varNames!==null && !array_key_exists($key,$varNames)) {
                    continue;
                }
                if(isset($model->$key)) {
                    $values[$key] = $model->$key;
                } else {
                    $values [ $key] = NULL;
                }
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
        if($forDatabase===false) {
            foreach ($this->newVariables as $key ) {
                if(isset($model->$key) && ($varNames===null || array_key_exists($key,$varNames))) {
                    $values[$key ] = $model->$key;
                }
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

    public function getColumnName(String $fieldName) {
        if(!empty($fieldName)) {
            if(isset($this->varKeys[$fieldName])) {
                return $this->varKeys[$fieldName];
            }
            if(in_array($fieldName,$this->tableKeys)) {
                return $fieldName;
            }
        }
        return '';
    }

    public function getFieldName(String $columnName) {
        if(!empty($columnName)) {
            if(isset($this->tblKeys[$columnName])) {
                return $this->tblKeys[$columnName];
            }
            if(in_array($columnName,$this->variableKeys)) {
                return $columnName;
            }
        }
        return '';
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

    private static function checkDataExits(Array $data,String $key,$defaultValue) {
        if(array_key_exists($key,$data) && !empty($data[$key])) {
          return $data[$key];
        }
        return $defaultValue;
      }

    public static function dataTablePagination($modelClassName,Array $data,?Connection $connection = NULL,QueryBuilder $builder = null,Closure $searchCallBack=null) {
        if($data===null) {
            throw new \Exception("Data should not be null", 1);       
        }
        if($connection === null) {
            $connection = new Connection();
        }
        $model = static::initalizeModel($modelClassName,null);
        $draw = empty($data['draw'])?0:$data['draw'];
          
          $dataTable = ["length"=>static::checkDataExits($data,'length',25),"start"=>static::checkDataExits($data,'start',0)];
          $columns = [];
          if(array_key_exists('columns',$data)) {
            foreach ($data['columns'] as $column) {
                $c = $model->modelHelper->getColumnName($column['data']);
                if(!empty($c)) {
                    $columns[]= $c;
                }
                
            }
          }
          if(!empty($data['order'])) {
            $columnIndex = static::checkDataExits($data['order'][0],'column',-1);
            if(array_key_exists('columns',$data) && $columnIndex>-1) {
              $c =  $model->getColumnName(static::checkDataExits($data['columns'][$columnIndex],'data','id'));
              if(empty($c)) {
                $dataTable['orderBy'] = $model->modelHelper->primaryColumn;
              } else {
                $dataTable['orderBy'] = $c;
              }
            } else {
              $dataTable['orderBy'] = $model->modelHelper->primaryColumn;
            }
            $order = static::checkDataExits($data['order'][0],'dir','asc');
            $dataTable['order'] =  \strtolower($order)=='desc'?'desc':'asc';
          } else {
            $dataTable['orderBy'] = $model->modelHelper->primaryColumn;
            $dataTable['order'] = 'asc';
          }
          if(array_key_exists('search',$data)) {
            $dataTable['search'] = static::checkDataExits($data['search'],'value','');
          }
          
          if($builder === null) {
            $builder = $connection->getQueryBuider();
          }
          $totalRecords = $connection->fetchAssoc($connection->query($builder->getQuery($model->modelHelper->table,['COUNT('.$model->modelHelper->primaryColumn.') AS count'])));
          if($totalRecords) {
            $totalRecords = $totalRecords['count'];
          } else {
            $totalRecords = 0;
          }
          if(isset($dataTable['search']) && !empty(trim($dataTable['search']))) {
              $search = $dataTable['search'];
              if($searchCallBack!==null) {
                $searchCallBack($builder,$search);
              } else {
                  if(empty($columns)) {
                    $columns = $model->modelHelper->tableKeys;
                  }
                  if(!empty($builder->getWhere())) {
                    $builder->and();
                  }
                  $builder->startgroup();
                  foreach ($columns as $index => $column) {
                    if($index!=0) {
                        $builder->or();
                    }
                    $builder->like($column,'%'.$search.'%');
                  }
                  $builder->endgroup();
              }
              
            
          }
          $totalRecordwithFilter = $connection->fetchAssoc($connection->query($builder->getQuery($model->modelHelper->table,['COUNT('.$model->modelHelper->primaryColumn.') AS count'])));
          if($totalRecordwithFilter) {
            $totalRecordwithFilter = $totalRecordwithFilter['count'];
          } else {
            $totalRecordwithFilter = 0;
          }
          $builder->limit($dataTable['length'],$dataTable['start']);
          $builder->orderBy($dataTable['orderBy'],$dataTable['order']==="asc");
          $models = static::findAllByQuery($modelClassName,$builder->getQuery($model->modelHelper->table),$connection);  
          return array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordwithFilter,
            "aaData" => $models
          );
    }
    
}