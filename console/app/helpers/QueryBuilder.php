<?php 
namespace SFW\Helpers;
use SFW\Connection;
class QueryBuilder {
    private $joins = array();
    private $limit = "";
    private $orderBy = array();
    private $groupBy = array();
    private $where="";
    private $andOrAdded=false;
    private $groupCount=0;
    private $distinct=false;

    static function get(Connection $dbConnection) {
        return new QueryBuilder($dbConnection);
    } 

    public function __construct(Connection $dbConnection) {
        $this->connection = $dbConnection;
    }


    function distinct() {
        $this->distinct=true;
        return $this;
    }


    function leftJoin(String $table,String $leftCondition,String $rightCondition,String $operator='=') {
        return $this->addJoin("LEFT JOIN",$table,$leftCondition,$rightCondition,$operator);
    }
    function leftMultiJoin(String $table,String $condition) {
        return $this->addMultiJoin("LEFT JOIN",$table,$condition);
    }
    function leftInnerJoin(String $table,String $leftCondition,String $rightCondition,String $operator='=') {
        return $this->addJoin("LEFT INNER JOIN",$table,$leftCondition,$rightCondition,$operator);
    }

    function leftInnerMultiJoin(String $table,String $condition) {
        return $this->addMultiJoin("LEFT INNER JOIN",$table,$condition);
    }

    function leftOuterJoin(String $table,String $leftCondition,String $rightCondition,String $operator='=') {
        return $this->addJoin("LEFT OUTER JOIN",$table,$leftCondition,$rightCondition,$operator);
    }
    function leftOuterMultiJoin(String $table,String $condition) {
        return $this->addMultiJoin("LEFT OUTER JOIN",$table,$condition);
    }

    function rightJoin(String $table,String $leftCondition,String $rightCondition,String $operator='=') {
        return $this->addJoin("RIGHT JOIN",$table,$leftCondition,$rightCondition,$operator);
    }
    function rightMultiJoin(String $table,String $condition) {
        return $this->addMultiJoin("RIGHT JOIN",$table,$condition);
    }

    function rightInnerJoin(String $table,String $leftCondition,String $rightCondition,String $operator='=') {
        return $this->addJoin("RIGHT INNER JOIN",$table,$leftCondition,$rightCondition,$operator);
    }
    function rightInnerMultiJoin(String $table,String $condition) {
        return $this->addMultiJoin("RIGHT INNER JOIN",$table,$condition);
    }

    function rightOuterJoin(String $table,String $leftCondition,String $rightCondition,String $operator='=') {
        return $this->addJoin("RIGHT OUTER JOIN",$table,$leftCondition,$rightCondition,$operator);
    }
    function rightOuterMultiJoin(String $table,String $condition) {
        return $this->addMultiJoin("RIGHT OUTER JOIN",$table,$condition);
    }

    function innerJoin(String $table,String $leftCondition,String $rightCondition,String $operator='=') {
        return $this->addJoin("INNER JOIN",$table,$leftCondition,$rightCondition,$operator);
    }
    function innerMultiJoin(String $table,String $condition) {
        return $this->addMultiJoin("INNER JOIN",$table,$condition);
    }

    function outerJoin(String $table,String $leftCondition,String $rightCondition,String $operator='=') {
        return $this->addJoin("OUTER JOIN",$table,$leftCondition,$rightCondition,$operator);
    }
    function outerMultiJoin(String $table,String $condition) {
        return $this->addMultiJoin("OUTER JOIN",$table,$condition);
    }

    function join(String $table,String $leftCondition,String $rightCondition,String $operator='=') {
        return $this->addJoin("JOIN",$table,$leftCondition,$rightCondition,$operator);
    }
    function multiJoin(String $table,String $condition) {
        return $this->addMultiJoin("JOIN",$table,$condition);
    }

    function limit(int $limit,int $offset= -1) {
        if($limit===null || $limit<1 || $this->limit!='') {
            return $this;
        }
        if($offset!==null && $offset>-1)
             $this->limit="".$offset.", ";
        $this->limit .= $limit;
        return $this;
    }

    function orderBy(String $orderBy,bool $isAsc=true) {
        $this->orderBy[]=$this->connection->sanitize($orderBy)." ".($isAsc?"":"DESC");
        return $this;
    }

    
    function groupBy(String $groupBy) {
        $this->groupBy[]=$this->connection->sanitize($groupBy);
        return $this;
    }

    

    function and() {
        
        if(strlen($this->where)>0 && !$this->andOrAdded) {
            $this->where.=" AND ";
            $this->andOrAdded=true;
        }
        return $this;
    }

    function or() {
        if(strlen($this->where)>0 && !$this->andOrAdded) {
            $this->where.=" OR ";
            $this->andOrAdded=true;
        }
        return $this;
    }

    function startgroup() {
        $this->where.=" ( ";
        $this->groupCount++;
        return $this;
    }

    function endgroup() {
        if($this->groupCount>0) {
            $this->where.=" ) ";
            $this->groupCount--;
        }        
        return $this;
    }

    function notEqual(String $left,String $right,bool $isForAnd=true) {
        return $this->where($left,$right,'!=',$isForAnd);
    }

    function between(String $left,String $leftBetween,String $rightBetween,bool $isForAnd=true) {
        return $this->where($left,$leftBetween.' AND '.$rightBetween, ' BETWEEN ',$isForAnd,false);
    }

    function like(String $left,String $right,bool $isForAnd=true,bool $addSingleQuoteToValue = true) {
        return $this->where($left,$right,'LIKE',$isForAnd,$addSingleQuoteToValue);
    }

    function where(String $left,String $right,String $operator="=",bool $isForAnd=true,bool $addSingleQuoteToValue = true) {
        $this->checkAndOrOnWhere($isForAnd);
        if($addSingleQuoteToValue === true) {
            $this->where.=" ".$this->connection->sanitize($left).' '.$this->connection->sanitize($operator)." '".$this->connection->sanitize($right)."' ";
        } else {
            $this->where.=" ".$this->connection->sanitize($left).' '.$this->connection->sanitize($operator)." ".$this->connection->sanitize($right)." ";
        }
        
        $this->andOrAdded=false;
        return $this;
    }

    function whereByQuery(String $left,String $query,String $operator="=",bool $isForAnd=true) {
        $this->checkAndOrOnWhere($isForAnd);
        $this->where.=" ".$this->connection->sanitize($left).' '.$this->connection->sanitize($operator)." ".$this->connection->sanitize($query)." ";
        $this->andOrAdded=false;
        return $this;
    }

    function checkAndOrOnWhere(bool $isForAnd=true) {
        $length=strlen($this->where);//|| trim($this->where," ").substr("(", -$length)===trim($this->where," ")
        if(!$this->andOrAdded  && strlen($this->where) > ($this->groupCount * 3) ) {
            $this->where.=" ".($isForAnd?"AND ":"OR ");
            $this->andOrAdded=true;
            return $this;
        }
    
        return $this;
    }

    function orWhere(String $left,String $right,String $operator="=") {
        return $this->where($left,$right,$operator,false);
    }

    function andWhere(String $left,String $right,String $operator="=") {
        return $this->where($left,$right,$operator);
    }

    function IN(String $left,$values,bool $isForAnd=true) {
        $this->checkAndOrOnWhere($isForAnd);
        $this->where.=" ".$this->connection->sanitize($left)." IN('".$this->connection->sanitize($values)."') ";
        $this->andOrAdded=false;
        return $this;
    }

    function isNull(String $left,bool $isForAnd=true) {
        return $this->whereByQuery($left,""," IS NULL ",$isForAnd);
    }

    function isNotNull(String $left,bool $isForAnd=true) {
        return $this->whereByQuery($left,""," IS NOT NULL ",$isForAnd);
    }

    function InNumeric(String $left,String $values,bool $isForAnd=true) {
        $this->checkAndOrOnWhere($isForAnd);
        $this->where.=" ".$this->connection->sanitize($left)." IN(".$this->connection->sanitize($values).") ";
        $this->andOrAdded=false;
        return $this;
    }

    function InByQuery(String $left,String $query,bool $isForAnd=true) {
        $this->checkAndOrOnWhere($isForAnd);
        $this->where.=" ".$this->connection->sanitize($left)." IN(".$query.") ";
        $this->andOrAdded=false;
        return $this;
    }

    private function addJoin(String $type,String $table,String $leftCondition,String $rightCondition,String $operator='=') {
        $this->joins[]=" ".$type." ".$this->connection->sanitize($table).' ON '.$this->connection->sanitize($leftCondition).$this->connection->sanitize($operator).$this->connection->sanitize($rightCondition)." ";
        return $this;
    }

    private function addMultiJoin(String $type,String $table,String $conditions) {
        $this->joins[]=" ".$type." ".$this->connection->sanitize($table).' ON '.$conditions." ";
        return $this;
    }

    function getJoinString() {
        return implode(" ",$this->joins);
    }

    function getGroupByString() {
        return implode(", ",$this->groupBy);
    }

    function getOrderByString() {
        return implode(", ",$this->orderBy);
    }

    function validate() {
        if($this->andOrAdded) {
            throw new \Exception("AND | OR keyword added. But right side condition is empty.", 1);    
            exit;        
        }
        if($this->groupCount>0) {
            throw new \Exception("GROUP started but not ended.  Count = ".$this->groupCount, 1);  
            exit;          
        }
    }

    function getWhere() {
        $this->validate();
        return $this->where;
    }

    function getQuery(String $table,Array $columns=array()) {
        $this->validate();
        $query="SELECT ";
        if($this->distinct)
            $query.=" DISTINCT ";
        $query.=(empty($columns)?"*":implode(",", $columns))." FROM ".$this->connection->sanitize($table);
        
        if(!empty($this->joins)) {
            $query.=$this->getJoinString();
        }

        if($this->where != "") {
            $query.=" WHERE ".$this->where;
        }      

        if(!empty($this->groupBy)) {
            $query.=" GROUP BY ".$this->getGroupByString()." ";
        } 

        if(!empty($this->orderBy)) {
            $query.=" ORDER BY ".$this->getOrderByString()." ";
        } 

        if($this->limit != "") {
            $query.=" LIMIT ".$this->limit;
        }
        
        return $query;   
    }


    function insertQuery(String $tablename,Array $arraydata,bool $addCreateUpdateDate=true) {
       if(!$addCreateUpdateDate && count($arraydata)==0)
           throw new \Exception("You should provide minimum one column with value", 1);
       
       $sql = 'INSERT INTO '.$this->connection->sanitize($tablename).' (';
       $values = "VALUES";
   
       $isMultiDimentional=false;
       foreach ($arraydata as $aa) {
           $isMultiDimentional=is_array($aa);
           if($isMultiDimentional) {
               if($addCreateUpdateDate === true && empty($aa['created_at'])) {
                   $aa['created_at']='now()';
               }
               if($addCreateUpdateDate === true && empty($aa['updated_at'])) {
                    $aa['updated_at']='now()';
                }
               foreach ($aa as $ab => $ac) {  
                   $sql.=$this->connection->sanitize($ab).',';
               }
           }
           break;
       }
   
       
       if($isMultiDimentional === false)
           $values.="(";
   
       if($isMultiDimentional === false && $addCreateUpdateDate === true  && empty($aa['created_at'])) {
           $arraydata['created_at']='now()';
       }
       if($isMultiDimentional === false && $addCreateUpdateDate === true  && empty($aa['updated_at'])) {
            $arraydata['updated_at']='now()';
        }
       foreach ($arraydata as $key => $val) {
           if ($isMultiDimentional) {
               if($addCreateUpdateDate === true && empty($aa['created_at'])) {
                   $val['created_at']='now()';
               }
               if($addCreateUpdateDate === true  && empty($aa['updated_at'])) {
                    $val['updated_at']='now()';
                }
                if(count($val)==0) {
                   continue;
               }
               $values.="(";
               foreach ($val as $k => $v) {  
                   
                   if(strrpos( $v,")")==strlen($v)-1)
                       $values .= $this->connection->sanitize($v).",";
                   else
                       $values .= "'".$this->connection->sanitize($v)."',";
               }
               $values=substr($values,0,-1);
               $values.="),";
           } else {
               
               $sql.=$this->connection->sanitize($key).',';
               if(strrpos( $val,")")==strlen($val)-1)
                   $values .= $this->connection->sanitize($val).",";
               else
                   $values .= "'".$this->connection->sanitize($val)."',";
           }       
       }
   
       $sql = substr($sql,0,-1).")".substr($values,0,-1);
       if($isMultiDimentional === false)
           $sql.=")";
       return $sql;        
   }

    function updateQuery(String $table,Array $keyValues=array(),bool $addUpdatedDate = true,String $tableAlias = '') {
        $sql = 'UPDATE '.$this->connection->sanitize($table).' '.(trim($tableAlias)==''?'':' AS '.$this->connection->sanitize($tableAlias).' ');
        $tableAlias = trim($tableAlias)==''?'':trim($tableAlias).'.';
         if($addUpdatedDate===true && empty($keyValues['updated_at'])) {
            $keyValues[$tableAlias.'updated_at']='now()';
        }
         if(count($keyValues)==0)
            throw new \Exception("You should provide minimum one column with value", 1);
        if(!empty($this->joins)) {
            $sql.=" ".$this->getJoinString();
        }    
        $sql.=" SET ";
        foreach ($keyValues as $key => $val) {
            $sql.=$tableAlias.$this->connection->sanitize($key);
            if(endsWith( $val,"()"))
                $sql .= ' ='.$this->connection->sanitize($val).",";
            else
                $sql.=' =\''.$this->connection->sanitize($val).'\',';
            
        }
        $sql = substr($sql,0,-1);

        
        if($this->where != "") {
            $sql.=" WHERE ".$this->where;
        } 
        return $sql;
    }

    function deleteQuery(String $table,String $tableAlias = '') {
        $query= "DELETE FROM ".$this->connection->sanitize($table).' '.(trim($tableAlias)==''?'':' AS '.$this->connection->sanitize($tableAlias).' ');
        if(!empty($this->joins)) {
            $query.=$this->getJoinString();
        }

        if($this->where != "") {
            $query.=" WHERE ".$this->where;
        }  
        return $query;   
    }

    function insertOrUpdateQuery(String $tablename,Array $insertArray,Array $updateArray,bool $addCreateUpdateDate=true) {
        $insertQuery = $this->insertQuery($tablename,$insertArray,$addCreateUpdateDate);
        if(count($updateArray)>0) {
            $insertQuery .= ' ON DUPLICATE KEY UPDATE ';
            if($addCreateUpdateDate===true && empty($updateArray['updated_at'])) {
                $updateArray['updated_at']='now()';
            }
            foreach ($updateArray as $key => $val) {
                $insertQuery .= $this->connection->sanitize($key);
                if(endsWith( $val,"()"))
                    $insertQuery .= ' ='.$this->connection->sanitize($val).",";
                else
                    $insertQuery .=' =\''.$this->connection->sanitize($val).'\',';
                
            }
            $insertQuery = substr($insertQuery,0,-1);
        }

        return $insertQuery;
        
    }
}