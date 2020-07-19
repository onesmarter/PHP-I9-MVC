<?php
namespace SFW\Routes;
use SFW\Route;
Route::group("LoginMiddleWare",function () {
    Route::post('login', 'UserController@login');
});

Route::group("AuthMiddleWare",function () {

});