<?php
namespace SFW\Routes;
use SFW\Route;

Route::group("LoginMiddleWare",function () {
    Route::post('login', 'UserController@login');
});

Route::group("AuthMiddleWare",function () {
    Route::post('setUserDataAsVerified', 'UserDataController@setVerified');
    Route::post('deleteUserData', 'UserDataController@setDeleted');
    Route::post('updateFieldSetting', 'SettingsController@changeStatusRequired');
    Route::post('updateMultipleFieldsSetting', 'SettingsController@changeMultipleStatusRequired');
});