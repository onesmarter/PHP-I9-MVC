<?php
namespace SFW;
        
class Connection {
  
    public function __construct(String $host = null,String $username = null,String $password = null,String $databaseName = null,String $hostPort = null) {
        $this -> dbHost = $host === null?DB_HOST:$host;
        $this -> dbUser = $username === null?DB_USER:$username;
        $this -> dbPass = $password === null?DB_PASS:$password;
        $this -> dbName = $databaseName === null?DB_NAME:$databaseName;
        $this -> dbHostPort = $hostPort === null?DB_HOST_PORT:$hostPort;
    }

    function __destruct() {
        $this->close(true);
    }

    private function connect() {
        if($this->isInTransactionMode()) {
            return;
        }
        //Create the connection
        $this -> connection = new \mysqli($this -> dbHost, $this -> dbUser, $this -> dbPass,$this -> dbName,$this -> dbHostPort);
        if ($this -> connection -> connect_errno) {
            throw new \Exception($this -> connection -> connect_errno, 1);
        } 
        $this->isConnected = true;
        if(defined('SET_MYSQLI_OPTION') && SET_MYSQLI_OPTION == 1) {
            $this -> connection -> options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE,TRUE);
        }
    }

    public function getConnection() {
        $this->connect();
        return $this->connection;
    }

    public function isInTransactionMode() {
		return isset($this->transactionStarted) && $this->transactionStarted;
    }

    public function isDbConnected() {
        return isset($this->isConnected) && $this->isConnected;
    }

    public function isDbClosed() {
        return !$this->isDbConnected();
    }
    
    public function beginTransaction() {
		if ($this->isInTransactionMode()) {
			return;
		}
		$this->connect();
		$this->connection->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
		$this->transactionStarted = true;
    }
    
    public function commit($close = false) {
		if ($this->isInTransactionMode()) {
			$this->connection->commit();
			$this->transactionStarted = false;
			$this->close($close);
		}
	}

	public function rollback($close = false) {
		if ($this->isInTransactionMode()) {
			$this->connection->rollback();
			$this->transactionStarted = false;
			$this->close($close);
		}
	}

    public function query(String $query,bool $close=false  ) {
        $this -> connect();
        $result = $this->connection->query($query);
        $this -> close($close);
        return $result;
    }

    public function insert(String $query,bool $close=false ) {
        $this -> connect();
        $result = $this->connection->query( $query );
        if($result) {
            $id = $this->connection->insert_id;
        } else {
            $id = -1;
        }
        $this -> close($close);
        return $id;
    }

     
    public function update(String $query,bool $close=false ) {
        $this -> connect();
        $result = $this->connection->query( $query );
        $row = $result ? $this -> connection->affected_rows : 0;
        $this -> close($close);
        return $row;
    }

    public function delete(String $query,bool $close=false ) {
        return $this->update($query,$close);
    }

    public function insertId(bool $close=false) {
        $this -> connect();
        $id = $this->connection->insert_id;
        $this -> close($close);
        return $id;
    }

    public function close(bool $close=true) {
        if ($close === true && isset($this->isConnected) && $this->isConnected === true) {
            $this -> connection -> close();
            $this->isConnected = false;
        }
    }

    public function haveData( $result ) {
        return $result && $result !== false && $result->num_rows > 0;
    }

    public function totalRows( $result ) {
        return $result && $result !== false? $result->num_rows :0;
    }

    public function freeResult($result,$freeResult = true) {
        if($result && $result !== false && $freeResult===true) {
            try {
                $result->free_result();
            } catch (\Throwable $th) {
            }
            
        }
    }



    public function fetchArray( $result,bool $close = false,bool $freeResult = true) {
        if($result !== false) {
            $this -> connect();
            $row = $result->fetch_array();
            $this->freeResult($result,$freeResult);
            $this -> close($close);
            return $row;
        }
        return false;
    }

    public function fetchAssoc( $result,bool $close = false,bool $freeResult = true ) {
        if($result !== false) {
            $this -> connect();
            $row = $result->fetch_assoc();
            $this->freeResult($result,$freeResult);
            $this -> close($close);
            return $row;
        }
        return false;
    }

    public function getSingleData($result,bool $needAssoc=false,bool $close = false,bool $freeResult = true) {
        if ($result !== false &&  $data = ( $needAssoc ? $this->fetchAssoc( $result,$close,$freeResult ) : $this->fetchArray( $result,$close,$freeResult ))) {
            return $data;
        }
        return false;
    }

    public function getAll($result,bool $needAssoc=false,bool $close = false,bool $freeResult = true) {
        $fullData=array();
        if($result !== false) {
            $this -> connect();
            while ( $result &&  $data = ( $needAssoc ? $result->fetch_array() : $result->fetch_assoc() ) ) {
                $fullData[]= $data;
            } 
            $this->freeResult($result,$freeResult);
        }
        $this -> close($close);
        return $fullData;
    }

    public function getSingleByQuery(String $query,bool $needAssoc=false,bool $close = false,bool $freeResult = true) {
        return $this->getSingleData($this -> query($query),$needAssoc,$close,$freeResult);
    }

    public function getAllByQuery(String $query,bool $needAssoc=false,bool $close = false,bool $freeResult = true) {
        return $this->getAll($this -> query($query),$needAssoc,$close,$freeResult);
    }


    public function affectedRows(bool $close=false) {     
        $this -> connect();
        $rowCount = $this -> connection->affected_rows;
        $this -> close($close);
        return $rowCount;
    }

	
    
    

    //HELPER FUNCTIONS
    public function selectQuery(String $tablename,Array $conditionsArray=array(),Array $columns=array(),String $joins="",bool $sanitizeColumns=false) {
        $clms=empty($columns)?"*":"";
        for($i=0,$j=count($columns);$i<$j;$i++) {
            if($i!=0) {
                $clms.=",";
            }
            $clms.= $sanitizeColumns? $this->sanitize($columns[$i]) :  $columns[$i];
        }
    
        if(count($conditionsArray)<1) {
            return 'SELECT '.$clms.' FROM '.$this->sanitize($tablename).' '.$joins;
        }       
        $sql = 'SELECT '.$clms.' FROM '.$this->sanitize($tablename).' '.$joins.'  WHERE ';
        foreach ($conditionsArray as $key => $val) {
            $sql.=$this->sanitize($key).' =\''.$this->sanitize($val).'\' AND ';
        }
        $sql = substr($sql,0,-5);
        return $sql;
    }
    
    public function deleteQuery(String $tablename,Array $conditionsArray=array()) {
        if(count($conditionsArray)<1) {
            return 'DELETE FROM '.$this->sanitize($tablename).'';
        }       
        $sql = 'DELETE FROM '.$this->sanitize($tablename).' WHERE ';
        foreach ($conditionsArray as $key => $val) {
            $sql.=$this->sanitize($key).' =\''.$this->sanitize($val).'\' AND ';
        }
        $sql = substr($sql,0,-5);
        return $sql;
    }
    
    
    
    public function updateQuery(String $tablename,Array $arraydata,String $id,String $clm="id",bool $addupdated_ate=false) {       
         $sql = 'UPDATE '.$this->sanitize($tablename).' SET ';
         if($addupdated_ate) {
            $arraydata['updated_at']='now()';
        }
         if(count($arraydata)==0)
            throw new \Exception("You should provide minimum one column with value", 1);
            
        foreach ($arraydata as $key => $val) {
            $sql.=$this->sanitize($key);
            if(endsWith( $val,"()"))
                $sql .= ' ='.$this->sanitize($val).",";
            else
                $sql.=' =\''.$this->sanitize($val).'\',';
            
        }
        $sql = substr($sql,0,-1);
        $sql .=" where ".$this->sanitize($clm)." ='".$this->sanitize($id)."'";
        
        return $sql;
    }
    function insertQuery(String $tablename,Array $arraydata,bool $addCreateupdated_ate=false) {
        if(!$addCreateupdated_ate && count($arraydata)==0)
            throw new \Exception("You should provide minimum one column with value", 1);
        
        $sql = 'INSERT INTO '.$this->sanitize($tablename).' (';
        $values = "VALUES";
    
        $isMultiDimentional=false;
        foreach ($arraydata as $aa) {
            $isMultiDimentional=is_array($aa);
            if($isMultiDimentional) {
                if($addCreateupdated_ate === true && empty($aa['created_at'])) {
                    $aa['created_at']='now()';
                }
                if($addCreateupdated_ate === true && empty($aa['updated_at'])) {
                     $aa['updated_at']='now()';
                 }
                foreach ($aa as $ab => $ac) {  
                    $sql.=$this->sanitize($ab).',';
                }
            }
            break;
        }
    
        
        if($isMultiDimentional === false)
            $values.="(";
    
        if($isMultiDimentional === false && $addCreateupdated_ate === true  && empty($aa['created_at'])) {
            $arraydata['created_at']='now()';
        }
        if($isMultiDimentional === false && $addCreateupdated_ate === true  && empty($aa['updated_at'])) {
             $arraydata['updated_at']='now()';
         }
        foreach ($arraydata as $key => $val) {
            if ($isMultiDimentional) {
                if($addCreateupdated_ate === true && empty($aa['created_at'])) {
                    $val['created_at']='now()';
                }
                if($addCreateupdated_ate === true  && empty($aa['updated_at'])) {
                     $val['updated_at']='now()';
                 }
                 if(count($val)==0) {
                    continue;
                }
                $values.="(";
                foreach ($val as $k => $v) {  
                    
                    if(strrpos( $v,")")==strlen($v)-1)
                        $values .= $this->sanitize($v).",";
                    else
                        $values .= "'".$this->sanitize($v)."',";
                }
                $values=substr($values,0,-1);
                $values.="),";
            } else {
                
                $sql.=$this->sanitize($key).',';
                if(strrpos( $val,")")==strlen($val)-1)
                    $values .= $this->sanitize($val).",";
                else
                    $values .= "'".$this->sanitize($val)."',";
            }       
        }
    
        $sql = substr($sql,0,-1).")".substr($values,0,-1);
        if($isMultiDimentional === false)
            $sql.=")";
        return $sql;        
    }
    
    //start - functions for clean the input 
    public function cleanInput( $input ) {
    
        $search = array(
            '@<script[^>]*?>.*?</script>@si', // Strip out javascript
            '@<[\/\!]*?[^<>]*?>@si', // Strip out HTML tags
            '@<style[^>]*?>.*?</style>@siU', // Strip style tags properly
            '@<![\s\S]*?--[ \t\n\r]*>@',// Strip multi-line comments
            // "#[^\\\\\\\\]'#"   
        );
    
        $output = preg_replace( $search, '', $input );
        return $output;
    }

    public function escapeString($input) {
        $this -> connect();
        $output = $this -> connection->real_escape_string( $input  );
        return $output;
    }
    
    public function sanitize( $input) {
        
        if( is_array( $input ) ) {
            foreach( $input as $var => $val ) {
                $output[ $var ] = $this->sanitize( $val );
            }
        } else {
            if(PHP_7_4 === false &&  get_magic_quotes_gpc() ) {
                $input = stripslashes( $input );
            }
            $input  = $this->cleanInput( $input );
            $input = $this-> escapeString( $input  );
        }
        return $input;
    }
    
    public function sanitizeArray( $input ) {
        if( is_array( $input ) ) {
            $ret =array();
            foreach( $input as $var => $val ) {
                $ret[ $var ] = $this->sanitize( $val );
            }
            return $ret;
        }
        
        return $this->sanitize($input);
    }

    public function getQueryBuider() {
        return new \SFW\Helpers\QueryBuilder($this);
    }




}

?>