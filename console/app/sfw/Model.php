<?php
namespace SFW;

use Closure;
use SFW\Connection;
use SFW\Helpers\QueryBuilder;

abstract class Model implements \JsonSerializable,\ArrayAccess {
    private static Array $models = [];
    protected $table = '';
    public ?ModelHelper $modelHelper;
    protected ?Connection $connection = NULL;
    private Array $newVars = [];
    public function __construct(?Connection $connection = NULL) {
        if(array_key_exists(get_class($this),static::$models)) {
            $this->modelHelper = static::$models[get_class($this)];
        } else {
            if(empty($this->table)) {
                $table = $this->getClassName();
                if(endsWith($table,"Model")) {
                    $table = substr($table,0,strlen($table)-5);
                }
                $this->table = static::createTableName($table);
            }
            $this->setKeys();
            static::$models[get_class($this)] = $this->modelHelper;
        }
        $this->connection = $connection;

    }

    public static function createTableName($modelName): String {
        $newName = "tbl_";
        $previousChar = "_";
        for ($i=0,$j=strlen($modelName); $i < $j ; ++$i) { 
            if($previousChar!="_" && isUpperCase($modelName[$i])) {
                $previousChar = "_";
                $newName .= "_";
            }
            $newName .= strtolower($modelName[$i]);
        }
        if(!endsWith(strtolower($modelName),"s")) {
            $newName .= "s";
        }
        return $newName;
    }

    private function getClassName(): String {
        $reflect = new \ReflectionClass($this);
        return $reflect->getShortName();
    }

    

    private function setKeys() {
        $this->initializeConnection();
        
        $query = "SHOW COLUMNS FROM ".$this->table;
        $result = $this->connection->query($query);
        $primaryKey = NULL;
        $keys = [];
        while($row = $this->connection->fetchAssoc($result,false,false)) {
            $keys[] = $row['Field'];
            if($primaryKey==null && $row['Key']=='PRI') {
                $primaryKey = $row['Field'];
            }
        }
        $this->connection->freeResult($result);
        if($primaryKey==null) {
            throw new \Exception("Primary key should define for the table ".$this->table, 1);
        }
        $this->modelHelper = new ModelHelper($this->table,$primaryKey,$keys);
        
    }

    protected final function initializeConnection() {
        if($this->connection === null) {
            $this->connection = new Connection();
        }
    }

    public function getModelValues(bool $forDatabase = false,Array $variableNames = null): Array {
        return $this->modelHelper->getModelValues($this,$forDatabase,$variableNames);
    }

    public function getFieldsHaveValue(bool $forDatabase = false,Array $variableNames = null): Array {
        return $this->modelHelper->getFieldsHaveValue($this,$forDatabase,$variableNames);
    }

    public function arrayToModel(Array $array,Model &$model,bool $fromDatabase = false) {
        return $this->modelHelper->arrayToModel($array,$model,$fromDatabase);
    }

    public function getColumnName(String $fieldName) {
        return $this->modelHelper->getColumnName($fieldName);
    }

    public function getFieldName(String $columnName) {
        return $this->modelHelper->getFieldName($columnName);
    }



    public final function save(): Array {
        $this->initializeConnection();
        return $this->modelHelper->save($this,$this->connection);
    }

    public final function insert(): Array {
        $this->initializeConnection();
        return $this->modelHelper->insert($this,$this->connection);
    }

    public final function update(): Array {
        $this->initializeConnection();
        return $this->modelHelper->update($this,$this->connection);
    }

    public final function delete(): Array {
        $this->initializeConnection();
        return $this->modelHelper->delete($this,$this->connection);
    }

    public final function insertOrUpdate(): Array {
        $this->initializeConnection();
        return $this->modelHelper->insertOrUpdate($this,$this->connection);
    }

    public final function fetch() {
        $primaryKey = $this->modelHelper->primaryVariable;
        ModelHelper::findOne(get_class($this),$this->connection,[$this->modelHelper->primaryColumn=>$this->$primaryKey],null,true,array(),$this);
    }

    public static function find($primaryValue,?Connection $connection = NULL,Array $columns = array()) {
        return ModelHelper::find(get_called_class(),$primaryValue,$connection,$columns);
    }

    public static function findOne(?Connection $connection = NULL,Array $conditionArray= array(),String $orderBy = NULL,bool $ascending = true,Array $columns = array()) {
        return ModelHelper::findOne(get_called_class(),$connection,$conditionArray,$orderBy,$ascending,$columns);
    }

    public static function findOneByQuery(String $sqlQuery,?Connection $connection = NULL) {
        return ModelHelper::findOneByQuery(get_called_class(),$sqlQuery,$connection);
    }

    public static function findAll(?Connection $connection = NULL,Array $conditionArray=array(),Array $columns = array(),String $orderBy = NULL,bool $ascending = true,int $limit=0,int $offset=-1) {
        return ModelHelper::findAll(get_called_class(),$connection,$conditionArray,$columns,$orderBy,$ascending,$limit,$offset);
    }

    public static function findAllByQuery(String $sqlQuery,?Connection $connection = NULL) {
        return ModelHelper::findAllByQuery(get_called_class(),$sqlQuery,$connection);
    }

    public static function pagination(?Connection $connection = NULL,Array $conditionArray=array(),Array $columns = array(),String $orderBy = NULL,bool $ascending = true,int $limit=25,int $offset=0) {
        return ModelHelper::pagination(get_called_class(),$connection,$conditionArray,$columns,$orderBy,$ascending,$limit,$offset);
    }

    public static function dataTablePagination(Array $data,?Connection $connection = NULL,QueryBuilder $builder = null,Closure $searchCallBack=null) {
        return ModelHelper::dataTablePagination(get_called_class(),$data,$connection,$builder,$searchCallBack);
    }

    public function jsonSerialize() {
        return $this->modelHelper->getModelValues($this);
    }

    public function offsetSet($offset, $value) {
        if (!is_null($offset)) {
            $this->$offset = $value;
            $this->modelHelper->addNewVariable($offset);
        }
    }

    public function offsetExists($offset) {
        return isset($this->$offset);
    }

    public function offsetUnset($offset) {
        $this->modelHelper->removeNewVariable($offset);
        unset($this->$offset);
    }

    public function offsetGet($offset) {
        return isset($this->$offset) ? $this->$offset : null;
    }




}
