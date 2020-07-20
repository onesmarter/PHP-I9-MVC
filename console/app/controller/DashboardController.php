<?php

namespace SFW\Controller;

use QueryBuilder;
use SFW\Connection;
use SFW\Request;
use SFW\Models\UserDataModel;
use SFW\Controller\UserDataController;


class DashBoardController {
    

    public function amberList(Request $request,Connection $connection,$tpl) {
      return htmlResponse($tpl,['needCount'=>false,'heading'=>'Amber','models'=>(new UserDataController())->amberList($connection)],'after-login/dashboard/user-data-list');
    }
    public function redList(Request $request,Connection $connection,$tpl) {
      return htmlResponse($tpl,['needCount'=>false,'heading'=>'Red','models'=>(new UserDataController())->redList($connection)],'after-login/dashboard/user-data-list');
    }
    //Use autoVerifiedList instead
    public function greenList(Request $request,Connection $connection,$tpl) {
      return htmlResponse($tpl,['needCount'=>false,'heading'=>'Green','models'=>(new UserDataController())->greenList($connection)],'after-login/dashboard/user-data-list');
    }
    public function archiveList(Request $request,Connection $connection,$tpl) {
      return htmlResponse($tpl,['needCount'=>false,'heading'=>'Archive','models'=>(new UserDataController())->archiveList($connection)],'after-login/dashboard/user-data-list');
    }
    public function verifyList(Request $request,Connection $connection,$tpl) {
      $controller = new UserDataController();
      $models = $controller->verifyList($connection);
      $data = $controller->listCount($connection);
      $data['models'] = $models;
      $data['needCount'] = true;
      $data['heading'] = 'Verify';
      return htmlResponse($tpl,$data,'after-login/dashboard/user-data-list');
    }
    public function verifiedList(Request $request,Connection $connection,$tpl) {
      return htmlResponse($tpl,['needCount'=>false,'heading'=>'Verified','models'=>(new UserDataController())->verifiedList($connection)],'after-login/dashboard/user-data-list');
    }
    public function deletedList(Request $request,Connection $connection,$tpl) {
      return htmlResponse($tpl,['needCount'=>false,'heading'=>'Rejected','models'=>(new UserDataController())->deletedList($connection)],'after-login/dashboard/user-data-list');
    }
    public function autoVerifiedList(Request $request,Connection $connection,$tpl) {
      return htmlResponse($tpl,['needCount'=>false,'heading'=>'Auto Verified','models'=>(new UserDataController())->autoVerifiedList($connection)],'after-login/dashboard/user-data-list');
    }
}