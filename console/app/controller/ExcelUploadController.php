<?php

namespace SFW\Controller;
use SFW\Connection;
use SFW\Request;
use RainTPL;
use SFW\Controller\ExcelReaderController;

/** 
 * AUTHOR : JINTO PAUL
 * DATE   : 18/jun/2020
 * response =>   "success" OR "failed"
 * You can use sanitize() function before inserting values to db.. Now it is not implemented
 * tbl_required table structure 
    id => auto increment
    clm_name => Column name of the tbl_users_data
    original_name =>  for dashboard
    parent_clm_name => Use parent object clm_name if it is a child
    common_clm_name => Use a common column name for multiple column .. eg: us_citizen for us_citizen1,us_citizen2,us_citizen3,us_citizen4
    min_clm_select => Define value if the common_clm_name is not null
    max_clm_select => Define value if the common_clm_name is not null
    show_in_dashboard =>  for dashboard
    is_required => 0 OR 1 
**/

class ExcelUploadController {

    //Not updating the status if user already found
    public function uploadView(Request $request,Connection $connection,RainTPL $tpl) {
        return $tpl->draw('common/upload-excel',true);
    }

    //Not checking the status
    public function validateUser(Request $request,Connection $connection) {
        if(empty($request->data['userId']) || !is_numeric($request->data['userId'])) {
            return jsonResponse([],0,"Please provide valid details");
        }
        $msg = (new ExcelReaderController($connection))->validateUser($request->data['userId']);
        return jsonResponse(null,1,$msg);
    }

    //Not checking the status
    public function validateAllUsers(Request $request,Connection $connection) {
        $connection->beginTransaction();
        $connection ->getConnection()-> autocommit(FALSE);
        $status = "success";
        try {
            $msg = (new ExcelReaderController($connection))->validateAllUser();
        } catch (\Throwable $th) {
            if(isInDebugMode()) {
                print_r($th);
            }
            $status = "failed";
        }
        try {
            if ($status != "success" || !$connection -> commit()) {
                $connection -> rollback();
            }
        } catch (\Throwable $th) {
            if(isInDebugMode()) {
                print_r($th);
            }
            try {
                $connection -> rollback();
            } catch (\Throwable $th) {
            }
        }
        return jsonResponse(null,$status == "success"?1:0 ,$status == "success"?$msg:"Updation failed");
    }

    public function upload(Request $request,Connection $connection,RainTPL $tpl) {
        $connection->beginTransaction();
        $connection ->getConnection()-> autocommit(FALSE);
        $status = "success";
        try {
            $this->readFileArray($connection ,$request->files);
        } catch (\Throwable $th) {
            if(isInDebugMode()) {
                print_r($th);
            }
            $status = "failed";
        }
        try {
            if ($status != "success" || !$connection -> commit()) {
                $connection -> rollback();
            }
        } catch (\Throwable $th) {
            if(isInDebugMode()) {
                print_r($th);
            }
            try {
                $connection -> rollback();
            } catch (\Throwable $th) {
            }
        }
        return $status;
    }


    //You can upload multiple files or single file
    //Can use any key
    //The uploaded file will be delete after the transaction 
    
    private function readFileArray($connection,$files) {
        
        if(empty($files)) {
            throw new \Exception("Error Processing Request", 1); 
        }
        foreach($files as $file) {
            if(empty($file['name'])) {
                $this->readFileArray($connection,$file);
                return;
            }
            $isArray= is_array($file['type']);
            
            // $fileType=$isArray?$file['type'][0]:$file['type']; 
           
            $fileName = HOST_PATH."excel-files/".time().($isArray?$file['name'][0]:$file['name']);
            // $fileSize =$isArray?$file['size'][0]:$file['size'];
            $file =$isArray?$file['tmp_name'][0]:$file['tmp_name'];
            move_uploaded_file($file,$fileName);
            try {
                new ExcelReaderController($connection,$fileName);
            } catch (\Throwable $th) {
                unlink($fileName);
                throw $th;          
            }
            unlink($fileName);
            
        }
    }
}