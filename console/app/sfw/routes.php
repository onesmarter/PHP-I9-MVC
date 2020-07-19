<?php
use SFW\Route;

Route::get('createModel/{model}/{table}', 'CreateController@createModel','app/sfw')->setNameSpace("\SFW");
Route::get('createModel/{model}', 'CreateController@createModel','app/sfw')->setNameSpace("\SFW");
Route::get('createController/{controller}', 'CreateController@createController','app/sfw')->setNameSpace("\SFW");
Route::get('createMiddleWare/{middleware}', 'CreateController@createMiddleWare','app/sfw')->setNameSpace("\SFW");