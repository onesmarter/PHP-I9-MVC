<?php
use SFW\Route;

Route::get('createModel/{model}/{table}', 'ModelController@create','app/sfw')->setNameSpace("\SFW");