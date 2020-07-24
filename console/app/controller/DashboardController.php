<?php

namespace SFW\Controller;

use QueryBuilder;
use SFW\Connection;
use SFW\Request;
use SFW\Models\UserDataModel;
use SFW\Controller\UserDataController;


class DashBoardController {
    

    public function amberList(Request $request,Connection $connection,$tpl) {
      
      return htmlResponse($tpl,['needCount'=>false,'heading'=>'Amber','route'=>'amberPagination','removeOnVerify'=>'0','models'=>[]],'after-login/dashboard/user-data-list');
    }
    public function amberUnverifiedList(Request $request,Connection $connection,$tpl) {
      
      return htmlResponse($tpl,['needCount'=>false,'heading'=>'Amber','route'=>'amberUnverifiedPagination','removeOnVerify'=>'1','models'=>[]],'after-login/dashboard/user-data-list');
    }
    public function amberVerifiedList(Request $request,Connection $connection,$tpl) {
      
      return htmlResponse($tpl,['needCount'=>false,'heading'=>'Amber','route'=>'amberVerifiedPagination','removeOnVerify'=>'0','models'=>[]],'after-login/dashboard/user-data-list');
    }
    public function redList(Request $request,Connection $connection,$tpl) {
      return htmlResponse($tpl,['needCount'=>false,'heading'=>'Red','route'=>'redPagination','removeOnVerify'=>'0','models'=>[]],'after-login/dashboard/user-data-list');
    }
    public function redUnverifiedList(Request $request,Connection $connection,$tpl) {
      return htmlResponse($tpl,['needCount'=>false,'heading'=>'Red','route'=>'redUnverifiedPagination','removeOnVerify'=>'1','models'=>[]],'after-login/dashboard/user-data-list');
    }
    public function redVerifiedList(Request $request,Connection $connection,$tpl) {
      return htmlResponse($tpl,['needCount'=>false,'heading'=>'Red','route'=>'redVerifiedPagination','removeOnVerify'=>'0','models'=>[]],'after-login/dashboard/user-data-list');
    }
   
   
    public function archiveList(Request $request,Connection $connection,$tpl) {
      return htmlResponse($tpl,['needCount'=>false,'heading'=>'Archive','route'=>'archivePagination','removeOnVerify'=>'0','models'=>[]],'after-login/dashboard/user-data-list');
    }
    
    public function verifyList(Request $request,Connection $connection,$tpl) {
      $controller = new UserDataController();
      $data = $controller->listCount($connection);
      $data['models'] = [];
      $data['needCount'] = true;
      $data['heading'] = 'Verify';
      $data['route'] = 'verifyPagination';
      $data['removeOnVerify'] = '1';
      return htmlResponse($tpl,$data,'after-login/dashboard/user-data-list');
    }
    public function verifiedList(Request $request,Connection $connection,$tpl) {
      return htmlResponse($tpl,['needCount'=>false,'heading'=>'Verified','route'=>'verifiedPagination','removeOnVerify'=>'0','models'=>[]],'after-login/dashboard/user-data-list');
    }
    public function deletedList(Request $request,Connection $connection,$tpl) {
      return htmlResponse($tpl,['needCount'=>false,'heading'=>'Rejected','route'=>'deletedPagination','removeOnVerify'=>'0','models'=>[]],'after-login/dashboard/user-data-list');
    }
    public function autoVerifiedList(Request $request,Connection $connection,$tpl) {
      return htmlResponse($tpl,['needCount'=>false,'heading'=>'Auto Verified','route'=>'autoVerifiedPagination','removeOnVerify'=>'0','models'=>[]],'after-login/dashboard/user-data-list');
    }


    private function pagination($tpl,$data,$heading) {
      // $data = json_decode(json_encode($data),true);
      for ($i=0,$j=count($data['aaData']); $i <$j ; ++$i) { 
          $D = $data['aaData'][$i];//echo json_encode($D['errorList']);exit();
          
          $html = htmlResponse($tpl,['model'=>$D,'heading'=>$heading],'after-login/dashboard/user-data-pagination');
          
          $data['aaData'][$i]['id'] = $html; 
          unset($data['aaData'][$i]['errorList']);
          unset($data['aaData'][$i]['statusData']);
          unset($data['aaData'][$i]['content']);
      }
      return json_encode($data);
    }

    public function amberPagination(Request $request,Connection $connection,$tpl) {
      return $this->pagination($tpl,(new UserDataController())->amberList($request,$connection),"Amber");
    }
    public function amberVerifiedPagination(Request $request,Connection $connection,$tpl) {
      return $this->pagination($tpl,(new UserDataController())->amberVerifiedList($request,$connection),"Amber");
    }
    public function amberUnverifiedPagination(Request $request,Connection $connection,$tpl) {
      return $this->pagination($tpl,(new UserDataController())->amberUnverifiedList($request,$connection),"Amber");
    }
    public function redPagination(Request $request,Connection $connection,$tpl) {
      return $this->pagination($tpl,(new UserDataController())->redList($request,$connection),"Red");
    }
    public function redVerifiedPagination(Request $request,Connection $connection,$tpl) {
      return $this->pagination($tpl,(new UserDataController())->redVerifiedList($request,$connection),"Red");
    }
    public function redUnverifiedPagination(Request $request,Connection $connection,$tpl) {
      return $this->pagination($tpl,(new UserDataController())->redUnverifiedList($request,$connection),"Red");
    }
    public function archivePagination(Request $request,Connection $connection,$tpl) {
      return $this->pagination($tpl,(new UserDataController())->archiveList($request,$connection),"");
    }
    public function verifyPagination(Request $request,Connection $connection,$tpl) {
      $controller = new UserDataController();
      return $this->pagination($tpl,$controller->verifyList($request,$connection),"");
    }
    public function verifiedPagination(Request $request,Connection $connection,$tpl) {
      return $this->pagination($tpl,(new UserDataController())->verifiedList($request,$connection),"");
    }
    public function deletedPagination(Request $request,Connection $connection,$tpl) {
      return $this->pagination($tpl,(new UserDataController())->deletedList($request,$connection),"Rejected");
    }
    public function autoVerifiedPagination(Request $request,Connection $connection,$tpl) {
      return $this->pagination($tpl,(new UserDataController())->autoVerifiedList($request,$connection),"Auto Verified");
    }
}