<?php

namespace SFW\Controller;
use SFW\Connection;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use SFW\Helpers\QueryBuilder;
use SFW\Models\RequiredModel;
use SFW\Models\UserDataModel;

class ExcelReaderController {
    public function __construct(Connection $connection,$fileName = null) {
        $this->connection=$connection;
        
        $this->columns = [];
        //to store the data of the validated columns. 
        //will create a new array for each row data
        $this->validatedColumns=[];
        //to store the data of the excel columns. 
        //will create a new array for each row data
        $this->clmValues=[];
        //data from the tbl_required table
        $this->requiredColumns = [];
        $this->allRequiredColumns = [];
        if($fileName!=null) {
            $this->readExcelFile($fileName);
        }
        
    }

    public function setColumns() {
        //For identify excel file columns
        $engLetters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"; // FOR EXCEL COLUMN NAMES
        //Getting database column names and the curresponding excel sheet column name
        //$k => to identify 2 letters columns  . eg: AA,AB.....
        for ($i=0,$j=strlen( $engLetters),$k=-1; $i<$j; ++$i) { 
            $letter = ($k>-1?$engLetters[$k]:"").$engLetters[$i]."1";
            $column = $this->activeSheet->getCell($letter)->getValue();
            if(!empty($column)){
                $column = str_replace(".","", $column);
                $column = str_replace("'","", $column);
                $column = strtolower( $column);
                $this->columns[$column] = ($k>-1?$engLetters[$k]:"").$engLetters[$i];
                
                if($i+1>=$j) {
                    //END OF sigle letter columns..  Now starting 2nd letters columns..
                    $i=-1;
                    ++$k;
                }   
            } else if($k>-1 || $i!=0 ) {
                return [$i-1,$k,$letter];
            }
        }
    }

    public function readExcelFile($fileName) {
        $objExcel = \PhpOffice\PhpSpreadsheet\IOFactory::load($fileName); 
        
        //Set the active sheet
        $this->activeSheet = $objExcel->getActiveSheet();
       
        $this->setColumns();
        //pdf_path and pdf_name are mandatory
        if(empty($this->columns['pdf_path']) || empty($this->columns['pdf_name'])) {
            throw new \Exception('Pdf path/name column not found');
        }
        $this->setRequiredColumns();
        $this->read();
    }

    //For update a user according to the tbl_required data changes
    public function validateUser($userRowId) {
        if ($userRowId && is_numeric($userRowId)) {
            if($model=UserDataModel::find($userRowId,$this->connection)) {
                $this->columns = json_decode($model->content,true);
                $this->clmValues = json_decode($model->content,true);
                $this->setRequiredColumns();
                $this->insertData($model->pdfId,$model->pdfPath,$model->pdfName,$model->processStartTime);
                return "Successfully updated";
            }
            return "User Not Found";       
        }
        return "User Not Found";
    }

    //For update all user according to the tbl_required data changes
    public function validateAllUser() {
        $queryResult = $this->connection->query("SELECT * FROM tbl_users_data");
        while($data = $this->connection->fetchAssoc($queryResult,false,false)) {
            $this->columns = json_decode($data['content'],true);
            $this->clmValues = json_decode($data['content'],true);
            $this->setRequiredColumns();
            $this->insertData($data['pdf_id'],$data['pdf_path'],$data['pdf_name'],$data['process_start_time']);
        }
        $this->connection->freeResult($queryResult);
        return "Successfully updated";     
        
    }


    public function createNewColumns(String $fileName,Array $newColumns) {
        $objExcel = \PhpOffice\PhpSpreadsheet\IOFactory::load($fileName); 
        //For identify excel file columns
        $engLetters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"; // FOR EXCEL COLUMN NAMES
        //Set the active sheet
        $this->activeSheet = $objExcel->getActiveSheet();
        $columnData=$this->setColumns();
        $letterStart = $columnData[0];
        $k = $columnData[1];
        // $columnLetters = array_values($this->columns);
        // if(strlen( $columnLetters[count($columnLetters)-1])>1) {
        //     $k=strpos($engLetters,$columnLetters[count($columnLetters)-1][0]) ;
        //     $letterStart= (count($columnLetters)%26)+1;           
        // } else {
        //     $k= -1;
        //     $letterStart=0;
        // }
        //$k => to identify 2 letters colums  . eg: AA,AB.....
        for ($i=0,$j=count( $newColumns),$m=strlen( $engLetters); $i<$j; ++$i,++$letterStart) { 
            if(empty(trim($newColumns[$i]))) {
                continue;
            }
            $letter = ($k>-1?$engLetters[$k]:"").$engLetters[$letterStart]."1";
            $newValue =str_replace(" ","_",trim($newColumns[$i]));
            $this->columns[$newValue] = $letter;
            $this->activeSheet->getCell($letter)->setValue($newValue);
            if($letterStart+1>=$m) {
                //END OF sigle letter columns..  Now starting 2nd letters columns..
                $letterStart=-1;
                ++$k;
            } 
            
        }
        $writer = new Xlsx($objExcel);
        $writer->save($fileName);
        // $this->setRequiredColumns();
        // $newColumns = [];
        // foreach ($this->columns as $key => $value) {
        //     if(!array_key_exists($key,$this->allRequiredColumns)) {
        //         $newColumns [] = [$key,$value];
        //     }
        // }
        // echo "<br>". json_encode($newColumns);
    }


    private function setRequiredColumns() {
        if(!empty($this->requiredColumns)) {
            return;
        }
         
        $queryResult = $this->connection->query("SELECT * FROM tbl_required WHERE clm_name LIKE 'rule%' ORDER BY section_id ASC ");
        while($data = $this->connection->fetchAssoc($queryResult,false,false)) {
            $this->ruleGroups[$data['clm_name']]=$data;
        }
        $this->connection->freeResult($queryResult);
        $queryResult = $this->connection->query("SELECT * FROM tbl_required ORDER BY section_id ASC");
        // $notExists = [];
        //TO AVOID Notice: Undefined index: section_id in /Applications/MAMP/htdocs/I9-MVC/console/app/controller/ExcelReaderController.php on line 266 (validate function)
        $keyNotMatchingDatas = [];
        while($data = $this->connection->fetchAssoc($queryResult,false,false)) {
            if(!array_key_exists($data['clm_name'],$this->columns)) {
                // $notExists [] = $data['clm_name'];
                $keyNotMatchingDatas[$data['clm_name']] = $data;
                continue;
            }
            $data['excel-column']=$this->columns[$data['clm_name']];
            $data['children'] = [];
            if(!empty($data['parent_clm_name'])) {
                //check is parent data saved
                if(array_key_exists($data['parent_clm_name'],$this->requiredColumns)) {
                    //Update children
                    $this->requiredColumns[$data['parent_clm_name']]['children'][]=$data;
                } else {
                    //add parent data 
                    $this->requiredColumns[$data['parent_clm_name']]=['children'=>[$data]];
                }
            } else {
                //check is the column exists or not
                if(array_key_exists($data['clm_name'],$this->requiredColumns)) {
                    //If exists save the previous children
                    $data['children']=$this->requiredColumns[$data['clm_name']]['children'];
                }
                $this->requiredColumns[$data['clm_name']] = $data;  
                 
            }
            $this->allRequiredColumns[$data['clm_name']] = $data;
            if(array_key_exists($data['clm_name'],$this->requiredColumns)) {
                //If exists save the previous children
                $this->allRequiredColumns[$data['clm_name']]['children']=$this->requiredColumns[$data['clm_name']]['children'];
            }
        
        }
        $this->connection->freeResult($queryResult);
        //TO AVOID Notice: Undefined index: section_id in /Applications/MAMP/htdocs/I9-MVC/console/app/controller/ExcelReaderController.php on line 266 (validate function)
        foreach ($this->requiredColumns as $key => $value) {
            if(!isset($value['id']) && array_key_exists($key,$keyNotMatchingDatas)) {
                $this->requiredColumns[$key] = array_merge($keyNotMatchingDatas[$key],$this->requiredColumns[$key]);
                $this->allRequiredColumns[$key] = $this->requiredColumns[$key];
            } else if(!isset($value['id'])) {
                unset($this->requiredColumns[$key]);
            }
        }

        // echo "<br><br>".json_encode($notExists )."<br><br>";
    }


    private function read() {

        for ($i=2; true; ++$i) { 
            
            // $sql = "INSERT INTO `tbl_users_data` ";
            // $update = "UPDATE tbl_users_data SET ";
            // $clms = "";
            // $values = "";
            $this->validatedColumns=[];
            $this->clmValues=[];
            // $newColumns = [];
            foreach ($this->columns as $column => $value) {
                // if(!array_key_exists($column,$this->allRequiredColumns)) {
                //     $newColumns[] = $column;
                // }
                //No need to set the pdf file attributes to clmValues
                if($column=="pdf_id" || $column=="pdf_name" || $column=="pdf_path" || $column=="process_start_time" ) {
                    continue;
                }
                $cellValue = $this->activeSheet->getCell($value.$i)->getCalculatedValue();
                if($cellValue != "0" && ($cellValue=="" || trim($cellValue)=="")) {
                break 2;
                }
                $cellValue  = intval($cellValue);
                if($cellValue==-1) {
                    $cellValue = 0;//Previously it was 100
                }
                if($cellValue<0) {
                    // $cellValue = 0;
                    $cellValue *= -1;
                }
                $this->clmValues[$column]=$cellValue;
                
            } 
            // echo "<br><br>".json_encode($newColumns);die();
            
            $pdfName = $this->activeSheet->getCell($this->columns['pdf_name'].$i)->getCalculatedValue();
            $pdfPath = $this->activeSheet->getCell($this->columns['pdf_path'].$i)->getCalculatedValue();
            $pdfId = $this->activeSheet->getCell($this->columns['pdf_id'].$i)->getCalculatedValue();
            $pdfStartTime = $this->activeSheet->getCell($this->columns['process_start_time'].$i)->getCalculatedValue();
            if(empty($this->columns['pdf_path']) || empty($this->columns['pdf_name'])) {
                throw new \Exception('Pdf path/name column not found');
            }
            $this->insertData($pdfId,$pdfPath,$pdfName,$pdfStartTime);
        }
    }

    private function insertData($pdfId ,$pdfPath,$pdfName,$pdfStartTime) {
        $this->pdfName = $pdfName;
        $validatedData = $this->validate();
        $validatedData['allValidation'] = $this->validatedColumns;
        $queryResult = $this->connection->query("SELECT id FROM `tbl_users_data` WHERE pdf_path='".$pdfPath."' AND pdf_name='".$pdfName."'");
    
        $id = -1;
        $status = $validatedData['isValidationSuccess']?1:0;
        $statusString = "unverified";
        if(intval($validatedData['lowestScore'])>=80) {
            $statusString = "auto-verified";
        }
        if($user=$queryResult->fetch_assoc()) {
            $id= $user['id'];
            $update = "UPDATE tbl_users_data SET  pdf_id='".$this->sanitize($pdfId)."',process_start_time='".$this->sanitize($pdfStartTime)."', content='".$this->sanitize(json_encode($this->clmValues))."',status_data='".$this->sanitize(json_encode($validatedData))."',lowest_score='".$this->sanitize($validatedData['lowestScore'])."',lowest_column='".$this->sanitize($validatedData['lowestColumn'])."',is_success='".$this->sanitize($status)."',status='".$statusString."' WHERE id='".$id."'";
            $this->connection->query($update);
        } else {    
            $sql = "INSERT INTO tbl_users_data(pdf_id,pdf_path,pdf_name,process_start_time,content,status_data,lowest_score,lowest_column,is_success,status) VALUES('".$this->sanitize($pdfId)."','".$this->sanitize($pdfPath)."','".$this->sanitize($pdfName)."','".$this->sanitize($pdfStartTime)."','".$this->sanitize(json_encode($this->clmValues))."','".$this->sanitize(json_encode($validatedData))."','".$this->sanitize($validatedData['lowestScore'])."','".$this->sanitize($validatedData['lowestColumn'])."','".($this->sanitize($status))."','".$statusString."')";
            
            $id = $this->connection->query($sql);
             if(!$id || empty($id = $this->connection->insertId())) {
                 throw new \Exception("Error Processing Request", 1);          
             }
        }
        // $this->connection->freeResult($queryResult);
    }

    private function validate() {
        $columns = [];
        $unwantedKeys = ["pdf_id","pdf_path","process_start_time","pdf_name"];
        foreach ($this->requiredColumns as $column => $value) {
            if(in_array($column,$unwantedKeys)) {
                continue;
            }
            $columns[$value['section_id']][] = $column;
        }
        

        //SECTION 1   ====   EMPLOYEE INFORMATION
        //SECTION 2   ====   US CITIZEN
        //SECTION 3   ====   EMPLOYEE SIGNATURE
        //SECTION 4   ====   PREPARER OR TRANSLATOR
        //SECTION 5   ====   EMPLOYEE INFORMATION 2
        //SECTION 6   ====   LIST C
        //SECTION 7   ====   LIST B
        //SECTION 8   ====   LIST A
        //SECTION 9   ====   EMPLOYER INFORMATION
        //SECTION 10  ====   RE VERIFICATION 1
        //SECTION 11  ====   RE VERIFICATION 2
        //SECTION 12  ====   RE VERIFICATION 3
        //SECTION 13  ====   LIST A 2
        //SECTION 14  ====   LIST A 3
        
        $data = Array();
        
        //The initial lowest score should be greater than the maximum value. ie, 100
        //This helps to use $lowestScore>$data ["section".($i+1)]['lowestScore'] condition properly
        $lowestScore = 101;
        $lowestColumn="";
        $isValidationSuccess = true;
        $preparerSectionId=-1;
        $listAsectionId=-1;
        $listAsectionId_1=-1;
        $listAsectionId_2=-1;
        $listBsectionId=-1;
        $listCsectionId=-1;
        foreach ($columns as $key => $value) {
            $data ["section".$key] = $this->getSectionStatus($value,"section".$key);
            
            if(array_key_exists('minimum-select-error',$data ["section".$key]) && count($data ["section".$key]['minimum-select-error'])>0) {
                foreach ($data ["section".$key]['minimum-select-error'] as $error) {
                    if($data ["section".$key]['isValidationSuccess']) {
                        $data ["section".$key] = $this->addResponseData($data ["section".$key],0,$error['column'],false,$error['common_clm_name']);
                    }
                    $data ["section".$key]['failedColumns'][]=$error;
                    $this->validatedColumns[$error['column']] = 0;
                }
                if($data ["section".$key]['isValidationSuccess']) {
                    $firstError = $data["section".$key]['minimum-select-error'][0];
                    $data ["section".$key] = $this->addResponseData($data ["section".$key],0,$firstError['column'],false,$firstError['common_clm_name']);
                }
                $data["section".$key]['minimum-select-error'] = [];
            }
            $intKey = intval($key);
            if($preparerSectionId==-1 && in_array('preparer_translator_assisted',$value)) {
                $preparerSectionId = $intKey;
            }
            if($listAsectionId==-1 && in_array('lista_document_title',$value)) {
                $listAsectionId = $intKey;
            }
            if($listAsectionId_1==-1 && in_array('lista_document_title1',$value)) {
                $listAsectionId_1 = $intKey;
            }
            if($listAsectionId_2==-1 && in_array('lista_document_title2',$value)) {
                $listAsectionId_2 = $intKey;
            }
            if($listBsectionId==-1 && in_array('listb_document_title',$value)) {
                $listBsectionId = $intKey;
            }
            if($listCsectionId==-1 && in_array('listc_document_title',$value)) {
                $listCsectionId = $intKey;
            }
            if($listAsectionId != $intKey && $listAsectionId_1 != $intKey && $listAsectionId_2 != $intKey && $listBsectionId != $intKey && $listCsectionId != $intKey) {
            
                if($lowestScore>$data ["section".$key]['lowestScore'] && $data ["section".$key]['lowestColumn']!=null && $data ["section".$key]['lowestColumn']!="") {
                    $lowestScore = $data ["section".$key]['lowestScore'];
                    $lowestColumn = $data ["section".$key]['lowestColumn'];
                }
            }
            
            
            $isValidationSuccess = $isValidationSuccess && $data ["section".$key]['isValidationSuccess'];
        }
        
        //VALIDATE PREPARER LIST
        //preparer_emp_first_name will appear only if the document have multiple preparer
        if(array_key_exists('preparer_emp_first_name',$this->clmValues)) {
            for ($i=1; array_key_exists('preparer_last_name'.$i,$this->clmValues) ; ++$i) { 
                $validate = $this->getSectionStatus(['preparer_signature'.$i,'preparer_date'.$i,'preparer_last_name'.$i,'preparer_first_name'.$i,'preparer_address'.$i,'preparer_city'.$i,'preparer_state'.$i,'preparer_zip'.$i],"section");
                $data ["section".$preparerSectionId]['itemsCountThatHaveValue'] += $validate['section']['itemsCountThatHaveValue'];
                if(!$validate['section']['isValidationSuccess']) {
                    $data ["section".$preparerSectionId]['itemsCountThatHaveValue'] = false;
                    $data ["section".$preparerSectionId]['lowestScore'] = 0;
                    $data ["section".$preparerSectionId]['lowestColumn'] = $validate['section']['lowestColumn'];
                } else if($validate['section']['lowestScore']>$data ["section".$preparerSectionId]['lowestScore']) {
                    $validate['section']['lowestScore'] = $data ["section".$preparerSectionId]['lowestScore'];
                    $validate['section']['lowestColumn'] = $data ["section".$preparerSectionId]['lowestColumn'];
                }
                $isValidationSuccess = $isValidationSuccess && $validate ["section"]['isValidationSuccess'];
                
            }
            if($lowestScore>$data ["section".$preparerSectionId]['lowestScore']) {
                $lowestScore = $data ["section".$preparerSectionId]['lowestScore'];
                $lowestColumn = $data ["section".$preparerSectionId]['lowestColumn'];
            }
        }
        

        //SET total selected column count. ie, listA + listA1 + listA2 + listB + listC
        $listAtotalCount = $data['section'.$listAsectionId]['itemsCountThatHaveValue']+$data['section'.$listAsectionId_1]['itemsCountThatHaveValue']+$data['section'.$listAsectionId_2]['itemsCountThatHaveValue'];
        $totalListSelected = $listAtotalCount+$data['section'.$listBsectionId]['itemsCountThatHaveValue']+$data['section'.$listCsectionId]['itemsCountThatHaveValue'];
        $data['sectionABC'] = ["itemsCountThatHaveValue"=>$totalListSelected,"isValidationSuccess"=>true];
        if($this->ruleGroups['rule-4']['is_required']==1 && $this->ruleGroups['rule-5']['is_required']==1 && $this->ruleGroups['rule-6']['is_required']==1) {
            //VALIDATE list A, list A1 , list A2 , list B and list C
            //RULE no 1.  Select only list A OR list A1 OR list A2
            //Rule no 2.  OR Select both list B and list C
            //Rule no 3.  Select list A and Select only one document from list A

            

            
            $listACount = $data['section'.$listAsectionId]['itemsCountThatHaveValue'];
            $listA1Count = $data['section'.$listAsectionId_1]['itemsCountThatHaveValue'];
            $listA2Count = $data['section'.$listAsectionId_2]['itemsCountThatHaveValue'];
            $listBCount = $data['section'.$listBsectionId]['itemsCountThatHaveValue'];
            $listCCount = $data['section'.$listCsectionId]['itemsCountThatHaveValue'];
            //CHECK rule no 3
            if(($listACount>0 && $listA1Count>0) || ($listACount>0 && $listA2Count>0)  || ($listA2Count>0 && $listA1Count>0) ) {
                if($listACount>0 && !empty($data['section'.$listAsectionId]['lowestColumn'])) {
                    //LIST A..  If list A selected you can't set list B and list C data
                    $listLowestColumn =$data['section'.$listAsectionId]['lowestColumn'];
                } else if($listA1Count>0 && !empty($data['section'.$listAsectionId_1]['lowestColumn'])) {
                    //LIST A1..  If list A selected you can't set list B and list C data
                    $listLowestColumn =$data['section'.$listAsectionId_1]['lowestColumn'];
                } else {
                    //LIST A2..  If list A selected you can't set list B and list C data
                    $listLowestColumn =$data['section'.$listAsectionId_2]['lowestColumn'];
                }
                $data['sectionABC'] = ["reason"=>"Select any one doucument from list A ","lowestScore"=>0,"itemsCountThatHaveValue"=>$totalListSelected,"lowestColumn"=>$listLowestColumn,"lowestColumnCommonName"=>"","Column"=>"","lowestColumnCommonName"=>"","selectedItemCount"=>0,"isValidationSuccess"=>false];
            } else if(($listACount>0  || $listA1Count>0  || $listA2Count>0 ) && ($listBCount>0 || $listCCount>0)) {
                //RULE NO 1
                $reason = "LIST A selected. ";
                if($listBCount>0) {
                    $reason .= "LIST B selected. ";
                    $listLowestColumn = $data['section'.$listBsectionId]['lowestColumn'];
                } else {
                    $listLowestColumn = $data['section'.$listCsectionId]['lowestColumn'];
                }
                if($listCCount>0) {
                    $reason .= "LIST C selected. ";
                }
                $data['sectionABC'] = ["reason"=>"Select one of the document from list A OR Select both list B and list C. ".$reason,"lowestScore"=>0,"itemsCountThatHaveValue"=>$totalListSelected,"lowestColumn"=>$listLowestColumn,"lowestColumnCommonName"=>"","Column"=>"","lowestColumnCommonName"=>"","selectedItemCount"=>0,"isValidationSuccess"=>false];
            } else {
                
                if($listA2Count<=0 && $listA1Count<=0 && $listACount>0 && !$data['section'.$listAsectionId]['isValidationSuccess']) {
                    $data['sectionABC'] = ["reason"=>"Fill required columns of List A","lowestScore"=>0,"itemsCountThatHaveValue"=>$totalListSelected,"lowestColumn"=>$data['section'.$listAsectionId]['lowestColumn'],"lowestColumnCommonName"=>"","Column"=>"","lowestColumnCommonName"=>"","selectedItemCount"=>0,"isValidationSuccess"=>false];
                } else if($listACount<=0 && $listA2Count<=0 && $listA1Count>0 && !$data['section'.$listAsectionId_1]['isValidationSuccess']) {
                    $data['sectionABC'] = ["reason"=>"Fill required columns of List A1","lowestScore"=>0,"itemsCountThatHaveValue"=>$totalListSelected,"lowestColumn"=>$data['section'.$listAsectionId]['lowestColumn'],"lowestColumnCommonName"=>"","Column"=>"","lowestColumnCommonName"=>"","selectedItemCount"=>0,"isValidationSuccess"=>false];
                } else if($listACount<=0 && $listA1Count<=0 && $listA2Count>0 && !$data['section'.$listAsectionId_2]['isValidationSuccess']) {
                    $data['sectionABC'] = ["reason"=>"Fill required columns of List A2","lowestScore"=>0,"itemsCountThatHaveValue"=>$totalListSelected,"lowestColumn"=>$data['section'.$listAsectionId]['lowestColumn'],"lowestColumnCommonName"=>"","Column"=>"","lowestColumnCommonName"=>"","selectedItemCount"=>0,"isValidationSuccess"=>false];
                } else if(!$data['section'.$listBsectionId]['isValidationSuccess'] || !$data['section'.$listCsectionId]['isValidationSuccess'] ) {
                    //CHECK is lista is success or not
                    if(!$data['section'.$listAsectionId]['isValidationSuccess'] && !$data['section'.$listAsectionId_1]['isValidationSuccess'] && !$data['section'.$listAsectionId_2]['isValidationSuccess']) {
                        //RULE NO 2
                        if(!$data['section'.$listBsectionId]['isValidationSuccess'] ) {
                            $data['sectionABC'] = ["reason"=>"Fill required columns of both list B and list C","lowestScore"=>0,"itemsCountThatHaveValue"=>$totalListSelected,"lowestColumn"=>$data['section'.$listBsectionId]['lowestColumn'],"lowestColumnCommonName"=>"","Column"=>"","lowestColumnCommonName"=>"","selectedItemCount"=>0,"isValidationSuccess"=>false];
                        } else {
                            $data['sectionABC'] = ["reason"=>"Fill required columns of both list B and list C","lowestScore"=>0,"itemsCountThatHaveValue"=>$totalListSelected,"lowestColumn"=>$data['section'.$listBsectionId]['lowestColumn'],"lowestColumnCommonName"=>"","Column"=>"","lowestColumnCommonName"=>"","selectedItemCount"=>0,"isValidationSuccess"=>false];
                        }
                    }
                }
            }
        }

        $isValidationSuccess = $isValidationSuccess && $data['sectionABC']['isValidationSuccess'];
        
        if(array_key_exists('reason',$data['sectionABC'])) {
            $lowestScore = 0;
            $lowestColumn = $data ["sectionABC"]['lowestColumn'];
        } else {
            unset($data['section'.$listAsectionId]);
            unset($data['section'.$listAsectionId_1]);
            unset($data['section'.$listAsectionId_2]);
            unset($data['section'.$listBsectionId]);
            unset($data['section'.$listCsectionId]);
        }
        
        $lowestColumnOriginalName = "";
        if($lowestColumn!=null && $lowestColumn!="" && array_key_exists($lowestColumn,$this->allRequiredColumns)) {
            $lowestColumnOriginalName = $this->allRequiredColumns[$lowestColumn]['original_name'];
        }
        return ['lowestScore'=>$lowestScore,'lowestColumn'=>$lowestColumn,"lowestColumnOriginalName"=>$lowestColumnOriginalName,'isValidationSuccess'=>$isValidationSuccess,'section'=>$data];     

    }

    private function addResponseData(Array $response,int $lowScore,String $lowestColumn,bool $isValidationSuccess,String $lowestColumnCommonName) {
        $response['lowestScore']  = $lowScore;
        $response['lowestColumn']  = $lowestColumn;
        $response['isValidationSuccess']  = $isValidationSuccess;
        $response['lowestColumnCommonName']  = $lowestColumnCommonName;
        return $response;
    }

    private function getSectionStatus(Array $sessionKeys,String $identifier,Array $response = null,bool $isParentSelected = true) {
        if($response==null) {
            //Using camel case for keys. 
            //May use this values in javascript front end. 
            $response = ["lowestScore"=>101,"itemsCountThatHaveValue"=>0,"lowestColumn"=>"","lowestColumnCommonName"=>"","Column"=>"","lowestColumnCommonName"=>"","selectedItemCount"=>0,"isValidationSuccess"=>true];
        }
        $response['failedColumns'] = [];//To identify the validation failed columns
        foreach ($sessionKeys as $requiredKey => $originalKey) {
            if(is_numeric($requiredKey)) {
                $requiredKey = $originalKey;
            }
            $this->validatedColumns[$originalKey] = 1;
            if(is_array($requiredKey)) {
                return $this->getSectionStatus($requiredKey,$identifier,null,$isParentSelected);
            } else if(array_key_exists($requiredKey,$this->allRequiredColumns) && array_key_exists($originalKey,$this->clmValues)){
                
                $data =  array_key_exists($requiredKey,$this->requiredColumns)?$this->requiredColumns[$requiredKey]:$this->allRequiredColumns[$requiredKey];
                
                if($data['is_required']=='1') {
                    if($data['min_clm_select']!='0' || $data['max_clm_select']!='0' ) {
                        //OCR reading always have value if the type is checkbox. So we just ignore the selectedItemCount to avoid validation error
                        $response['selectedItemCount'] =1;// += empty($this->clmValues[$originalKey])?0:1;
                        $response['itemsCountThatHaveValue'] += empty($this->clmValues[$originalKey])?0:1;
                        //Validation will fail if the min count > selected item count OR max count < selected item count
                        if ($data['min_clm_select']!='0' && $response['selectedItemCount']<intval($data['min_clm_select'])  ) {
                            $errMsg = "Minimum Selected Item < ".$data['min_clm_select'];
                            $response['minimum-select-error'][]=['column'=>$originalKey,'originalName'=>$data ['original_name'],"reason"=>$errMsg,'common_clm_name'=>$data['common_clm_name'],'sectionId'=>$data['section_id']];
                        } else if ($data['max_clm_select']!='0' && $response['selectedItemCount']>intval($data['max_clm_select'])) {
                        
                            if($response['isValidationSuccess']) {
                                $response = $this->addResponseData($response,0,$originalKey,false,$data['common_clm_name']);
                            }
                            $response['failedColumns'][]=['column'=>$originalKey,'originalName'=>$data ['original_name'],"reason"=>"Maximum Selected Items > ".$data['max_clm_select'],'common_clm_name'=>$data['common_clm_name'],'sectionId'=>$data['section_id']];
                            $this->validatedColumns[$originalKey] = 0;
                            $response['minimum-select-error'] = [];
                        } else {
                            $response['minimum-select-error'] = [];
                        }
                    } else if(empty($this->clmValues[$originalKey])) {
                        if($isParentSelected) {
                            //No need to check the date if it is a child column and it's parent not selected
                            $response['failedColumns'][]=['column'=>$originalKey,'originalName'=>$data ['original_name'],"reason"=>"Empty Value",'common_clm_name'=>$data['common_clm_name'],'sectionId'=>$data['section_id']];
                            if($response['isValidationSuccess']) {
                                $response = $this->addResponseData($response,0,$originalKey,false,$data['common_clm_name']);
                            }
                            $this->validatedColumns[$originalKey] = 0;
                        }
                        
                       // return $response;
                    } else {

                        if(!$isParentSelected) {
                            //The parent should be selected to set child value.
                            $response = $this->addResponseData($response,0,$originalKey,false,$data['common_clm_name']);
                            $response['failedColumns'][]=['column'=>$originalKey,'originalName'=>$data ['original_name'],"reason"=>"Parent Not Selected",'common_clm_name'=>$data['common_clm_name'],'sectionId'=>$data['section_id']];
                            $this->validatedColumns[$originalKey] = 0;
                        }
                        $response['itemsCountThatHaveValue'] += 1;
                    }

                    // OCR reading always have value if the type is checkbox. 
                    // So we want  maximum value of the  checkbox to identify which checkbox is selected.
                    if($data['min_clm_select']!='0' || $data['max_clm_select']!='0' ) {
                        //SET highest value
                        if(!empty($this->clmValues[$originalKey]) && ($response['lowestScore']==101 || $response['lowestScore']<intval($this->clmValues[$originalKey]))) {
                            $response = $this->addResponseData($response,intval($this->clmValues[$originalKey]),$originalKey,$response['isValidationSuccess'],$data['common_clm_name']);
                        }
                    } else if(!empty($this->clmValues[$originalKey]) && $response['lowestScore']>intval($this->clmValues[$originalKey])) {
                    
                        $response = $this->addResponseData($response,intval($this->clmValues[$originalKey]),$originalKey,$response['isValidationSuccess'],$data['common_clm_name']);
                    }
                    
                    //Check is having child columns
                    //Need to check all the children [also if the $response['isValidationSuccess'] == false]
                    if(!empty($data['children']) ) {
                        $childKeys = [];
                        //FETCH ALL CHILD KEYS
                        foreach ($data['children'] as $child) {
                            $childKeys[] = $child['clm_name'];
                        }
                        //VALIDATE CHILDREN
                        $res = $this->getSectionStatus($childKeys,$identifier,null,!empty($this->clmValues[$originalKey]));
                        
                        $response['itemsCountThatHaveValue'] += $res['itemsCountThatHaveValue'];
                        $response['failedColumns'] = array_merge($response['failedColumns'],$res['failedColumns']);

                        //TODO: need to check minimum-select-error

                        //Children should not be select if the parent column is not selected
                        if($response['isValidationSuccess'] && empty($this->clmValues[$originalKey]) && $res['itemsCountThatHaveValue']>0) {
                            $response = $this->addResponseData($response,intval($this->clmValues[$originalKey]),$originalKey,false,$data['common_clm_name']);
                        }
                        if( !empty($this->clmValues[$originalKey]) && $response['isValidationSuccess'] && !$res['isValidationSuccess']) {
                            $response = $this->addResponseData($response,0,$originalKey,false,$data['common_clm_name']);
                        }
                    }
                }
            }
        }
        return $response;
    }

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

    
    public function sanitize( $input) {
        
        if( is_array( $input ) ) {
            foreach( $input as $var => $val ) {
                $output[ $var ] = $this->sanitize( $val );
            }
        } else {
           
            $input  = $this->cleanInput( $input );
            $input = $this->connection-> escapeString( $input  );
        }
        return $input;
    }
}