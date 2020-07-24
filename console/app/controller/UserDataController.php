<?php

namespace SFW\Controller;
use SFW\Connection;
use SFW\Helpers\QueryBuilder;
use SFW\Models\UserDataModel;
use SFW\Request;


class UserDataController {
    private function getParsedVerifyList(Array $list) {
        if(empty($list)) {
          return [];
        }
        foreach ($list as $index => $model) {
          $failedColumns = array();
          $statusData = json_decode($model->statusData,TRUE);
          foreach ($statusData['section'] as $sectionKey=>$section) {
            if(!empty($section['failedColumns'])) {//5 6 7 12 13
              $first = $section['failedColumns'][0];
              if($first['sectionId']!="5" && $first['sectionId']!="6" && $first['sectionId']!="7" && $first['sectionId']!='12' && $first['sectionId'] !='13') {
                $failedColumns[$sectionKey] = $section['failedColumns'] ;
              } 
              
              // foreach ($section['failedColumns'] as $failedColumn) {
              //   $failedColumns[] = $failedColumn;
              // }
            } else if($sectionKey=="sectionABC" && array_key_exists('reason',$section)) {
              $failedColumns['Section List A, List B and List C']=[["column"=> $section['lowestColumn'],
              "originalName"=> $section['reason'],
              "reason"=> $section['reason'],
              "common_clm_name"=> "lists",
              "sectionId"=> "100"]];
            }
          }
  
          $list[$index]->errorList = htmlspecialchars(json_encode($failedColumns));
        }
        return $list;
      }
      
      /**
       * @param $from - eg: 50.  will use only when $query === null
       * @param $to - eg: 90.  will use only when $query === null
       * @param $htmlFile -> Which html file to be render
       * @param $tpl -> RainTPL instance
       * @param $query -> Custom query
       */
      public function getList(int $from,int $to,Connection $connection,QueryBuilder $builder = null,Array $data) {
        if($builder === null) {
          $builder = $connection->getQueryBuider();
          $builder = $builder->where('status','deleted','!=')->between('lowest_score',$from,$to);
        }
        $models = UserDataModel::dataTablePagination($data,$connection,$builder,null);
        // $models = UserDataModel::dataTablePagination($data,$connection,$builder,function($queryBuilder,$searchString) {
        //   $queryBuilder->like('pdf_name','%'.$searchString.'%',' LIKE');
        // });
        $models['aaData']=$this->getParsedVerifyList($models['aaData']);
        return $models;
      }

      private function checkDataExits(Array $data,String $key,$defaultValue) {
        if(array_key_exists($key,$data) && !empty($data[$key])) {
          return $data[$key];
        }
        return $defaultValue;
      }
  
      public function amberList(Request $request,Connection $connection) {
        return $this->getList(60,79,$connection,null,$request->data);
      }
      public function amberVerifiedList(Request $request,Connection $connection) {
        $builder = $connection->getQueryBuider();
        $builder = $builder->where('status','verified')->between('lowest_score',60,79);
        return $this->getList(60,79,$connection,$builder,$request->data);
      }
      public function amberUnverifiedList(Request $request,Connection $connection) {
        $builder = $connection->getQueryBuider();
        $builder = $builder->where('status','unverified')->between('lowest_score',60,79);
        return $this->getList(60,79,$connection,$builder,$request->data);
      }
      public function redList(Request $request,Connection $connection) {
        return $this->getList(0,59,$connection,null,$request->data);
      }
      public function redVerifiedList(Request $request,Connection $connection) {
        $builder = $connection->getQueryBuider();
        $builder = $builder->where('status','verified')->between('lowest_score',0,59);
        return $this->getList(60,79,$connection,$builder,$request->data);
      }
      public function redUnverifiedList(Request $request,Connection $connection) {
        $builder = $connection->getQueryBuider();
        $builder = $builder->where('status','unverified')->between('lowest_score',0,59);
        return $this->getList(60,79,$connection,$builder,$request->data);
      }

      public function autoVerifiedList(Request $request,Connection $connection) {
        $builder = $connection->getQueryBuider();
        $query = $builder->where('status','auto-verified');
        return $this->getList(0,0,$connection,$query,$request->data);
      }
      public function archiveList(Request $request,Connection $connection) {
        $builder = $connection->getQueryBuider();
        $query = $builder->where('status','archived');
        return $this->getList(0,0,$connection,$query,$request->data);
      }
      public function verifyList(Request $request,Connection $connection) {
        $builder = $connection->getQueryBuider();
        $query = $builder->where('status','unverified');
        return $this->getList(0,0,$connection,$query,$request->data);
      }
      public function verifiedList(Request $request,Connection $connection) {
        $builder = $connection->getQueryBuider();
        $query = $builder->where('status','verified');
        return $this->getList(0,0,$connection,$query,$request->data);
      }
      public function deletedList(Request $request,Connection $connection) {
        $builder = $connection->getQueryBuider();
        $query = $builder->where('status','deleted');
        return $this->getList(0,0,$connection,$query,$request->data);
      }

      public function listCount(Connection $connection) {
        $status = [
          // "verifiedGreenCount"=>"`status` = 'verified' AND `lowest_score` BETWEEN 80 AND 100",
        // "unverifiedGreenCount"=>"`status` = 'unverified' AND `lowest_score` BETWEEN 80 AND 100",
        "verifiedRedCount"=>"`status` = 'verified' AND `lowest_score` BETWEEN 0 AND 59",
        "unverifiedRedCount"=>"`status` = 'unverified' AND `lowest_score` BETWEEN 0 AND 59",
        "verifiedAmberCount"=>"`status` = 'verified' AND `lowest_score` BETWEEN 60 AND 79",
        "unverifiedAmberCount"=>"`status` = 'unverified' AND `lowest_score` BETWEEN 60 AND 79",
        "unverifiedCount"=>"`status` = 'unverified'",
        "verifiedCount"=>"`status` = 'verified'",
        "autoVerifiedCount"=>"`status` = 'auto-verified'",
        "deletedCount"=>"`status` = 'deleted'"];
        $query = "SELECT ";
        foreach ($status as $varKey => $condition) {
          $query .= "SUM(CASE WHEN ".$condition." THEN 1 ELSE 0 END) AS '".$varKey."',";
        } 
        $query = substr($query,0,strlen($query)-1);
        $query .= " FROM tbl_users_data";
        return $connection->getSingleByQuery($query,true);
      }

      public function setVerified(Request $request,Connection $connection) {
        return $this->setStatus($request,$connection,'verified');
      }

      public function setDeleted(Request $request,Connection $connection) {
        return $this->setStatus($request,$connection,'deleted');
      }

      private function setStatus(Request $request,Connection $connection,String $status) {
        if(empty($request->data['userId']) || !is_numeric($request->data['userId'])) {
          return jsonResponse(null,0,"Please provide valid user details");
        }
        $model=UserDataModel::find($request->data['userId'],$connection,['id']);
        if($model) {
          $model->status = $status;
          $data = $model->update();
          if($data['count']>0) {
            return jsonResponse(null,1,"Successfully updated");
          }
          return jsonResponse(null,0,"Updation failed");
        }
        return jsonResponse(null,0,"User details not found");
      }
}