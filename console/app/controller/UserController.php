<?php

namespace SFW\Controller;
use SFW\Connection;
use SFW\Models\UserModel;
use SFW\Request;
use SFW\Validation;


class UserController {
    public function loginView(Request $request,Connection $connection,$tpl) {
         return $tpl->draw('before-login/login', $return_string = true);
    }

    public function login(Request $request,Connection $connection,$tpl) {
        
        if(empty($request->data['email']) || empty($request->data['password']) || strlen($request->data['password'])<6 || Validation::isEmailValid($request->data['email'])===false) {
            return jsonResponse(null,0,"Invalid email or password");
        }
        $user=UserModel::findOne($connection,['email'=>$request->data['email'],'password'=>md5($request->data['password'])]);
        if($user) {
            unset($user->password);
            startSession('user');
            $_SESSION['user'] = $user;
            return jsonResponse(["url"=>"verify"],1,"Successfully logged in");
        } 
        return jsonResponse(null,0,"Invalid email or passwordd");
    }

    public function logout(Request $request,Connection $connection,$tpl) {
        destroySession();
        header("location:login");
        exit();
    }
}