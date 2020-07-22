<?php

namespace SFW\Controller;
use SFW\Connection;
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
      public function getList(int $from,int $to,Connection $connection,String $query = null) {
          if($query === null) {
            $builder = $connection->getQueryBuider();
            $query = $builder->where('status','deleted','!=')->between('lowest_score',$from,$to)->getQuery('tbl_users_data');
          }
          
          return $this->getParsedVerifyList( UserDataModel::findAllByQuery($query,$connection));         
      }
  
      public function amberList(Connection $connection) {
        return $this->getList(60,79,$connection);
      }
      public function redList(Connection $connection) {
        return $this->getList(0,59,$connection);
      }
      //Use autoVerifiedList instead
      public function greenList(Connection $connection) {
        return $this->getList(80,100,$connection);
      }
      public function autoVerifiedList(Connection $connection) {
        $builder = $connection->getQueryBuider();
        $query = $builder->where('status','auto-verified')->getQuery('tbl_users_data');
        return $this->getList(0,0,$connection,$query);
      }
      public function archiveList(Connection $connection) {
        $builder = $connection->getQueryBuider();
        $query = $builder->where('status','archived')->getQuery('tbl_users_data');
        return $this->getList(0,0,$connection,$query);
      }
      public function verifyList(Connection $connection) {
        $builder = $connection->getQueryBuider();
        $query = $builder->where('status','unverified')->getQuery('tbl_users_data');
        return $this->getList(0,0,$connection,$query);
      }
      public function verifiedList(Connection $connection) {
        $builder = $connection->getQueryBuider();
        $query = $builder->where('status','verified')->or()->where('status','auto-verified')->getQuery('tbl_users_data');
        return $this->getList(0,0,$connection,$query);
      }
      public function deletedList(Connection $connection) {
        $builder = $connection->getQueryBuider();
        $query = $builder->where('status','deleted')->getQuery('tbl_users_data');
        return $this->getList(0,0,$connection,$query);
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