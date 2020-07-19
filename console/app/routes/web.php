<?php
namespace SFW\Routes;
use SFW\Route;
Route::group("LoginMiddleWare",function () {
    Route::get('login', 'UserController@loginView');
    
});

Route::group("AuthMiddleWare",function () {
    Route::get('dashboard', 'DashboardController@dashboard');
    Route::get('/', 'DashboardController@dashboard');
});