<?php
namespace SFW\Routes;
use SFW\Route;

/******************************************************************************************/
Route::group("LoginMiddleWare",function () {
    Route::get('login', 'UserController@loginView');
});
Route::group("AuthMiddleWare",function () {
    Route::get('verify', 'DashBoardController@verifyList');
    Route::get('verified', 'DashBoardController@verifiedList');
    Route::get('amber', 'DashBoardController@amberList');
    Route::get('red', 'DashBoardController@redList');
    Route::get('green', 'DashBoardController@greenList');
    Route::get('archive', 'DashBoardController@archiveList');
    Route::get('settings', 'SettingsController@settings');
    Route::get('logout', 'UserController@logout');
    
});

/******************************************************************************************/
