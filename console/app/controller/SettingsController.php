<?php

namespace SFW\Controller;
use SFW\Connection;
use SFW\Models\RequiredModel;
use SFW\Request;


class SettingsController {
    public function settings(Request $request,Connection $connection,$tpl) {
        $models=$this->getAllSettingFields($request,$connection,$tpl);
        $tpl->assign( "data", $models);
        return $tpl->draw('after-login/others/settings', $return_string = true);
    }

    public function getAllSettingFields(Request $request,Connection $connection,$tpl) {
        return RequiredModel::findAll($connection,[],['id','clm_name','original_name','section_id','is_required'],"section_id");
    }

    public function changeMultipleStatusRequired(Request $request,Connection $connection,$tpl) {
        if(empty($request->data['fields']) || !is_array($request->data['fields']) || count($request->data['fields'])==0) {
            return jsonResponse([],0,"Please provide valid details");
        }
        $totalCount = 0;
        $models = [];
        foreach ($request->data['fields'] as $field) {
            if(empty($field['fieldName']) || !isset($field['isRequired'])) {
                return jsonResponse($models,0,"Please provide valid details");
            }
            $request->data['fieldName']=$field['fieldName'];
            $request->data['isRequired']=$field['isRequired'];
            $data = $this->changeStatusRequired($request,$connection,$tpl,false);
            $totalCount += $data['count'];
            if($data['model']!==null) {
                $models[]=$data['model'];
            }
        }
        if($totalCount==0) {
            return jsonResponse($request->data['fields'],0,"Updation failed");
        }
        return jsonResponse($models,1,"Successfully updated");
    }

    public function changeStatusRequired(Request $request,Connection $connection,$tpl,$returnJson = true) {
        if(empty($request->data['fieldName']) || !isset($request->data['isRequired']) || $request->data['isRequired']===null || !is_numeric($request->data['isRequired']) || $request->data['isRequired']>1 || $request->data['isRequired']<0 ) {
            return jsonResponse(null,0,"Please provide valid details");
        }

        $model = RequiredModel::findOne($connection,['clm_name'=>$request->data['fieldName']]);
        if($model) {
            $model->isRequired = $request->data['isRequired'];
            $data = $model->update();
            if($returnJson!==true) {
                return $data['model']=$model;
            }
            if($data['count']>0) {
              return jsonResponse($model,1,"Successfully updated");
            }
            return jsonResponse($model,0,"Updation failed");
          }
          if($returnJson!==true) {
            return ['type'=>'update','count'=>0,'model'=>null];
          }
          return jsonResponse(null,0,"Details not found");
    }
}