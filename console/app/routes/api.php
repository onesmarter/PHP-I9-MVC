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
    Route::post('amberPagination', 'DashBoardController@amberPagination');
    Route::post('redPagination', 'DashBoardController@redPagination');
    Route::post('greenPagination', 'DashBoardController@greenPagination');
    Route::post('archivePagination', 'DashBoardController@archivePagination');
    Route::post('verifyPagination', 'DashBoardController@verifyPagination');
    Route::post('verifiedPagination', 'DashBoardController@verifiedPagination');
    Route::post('deletedPagination', 'DashBoardController@deletedPagination');
    Route::post('autoVerifiedPagination', 'DashBoardController@autoVerifiedPagination');
    Route::post('amberVerifiedPagination', 'DashBoardController@amberVerifiedPagination');
    Route::post('amberUnverifiedPagination', 'DashBoardController@amberUnverifiedPagination');
    Route::post('redVerifiedPagination', 'DashBoardController@redVerifiedPagination');
    Route::post('redUnverifiedPagination', 'DashBoardController@redUnverifiedPagination');
});