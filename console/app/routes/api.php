<?php
namespace SFW\Routes;
use SFW\Route;

/******************************************************************************************/
//EXAMPLES - Delete these
//eg. call ->  http://localhost:8888/RainTPLDemo/console/api/exampleTest/1/io/fff    1 for "id" and fff for "name"
Route::get('exampleTest/{id}/io', 'ExampleController@apiExample');
Route::get('exampleTest/{id}/io/{name}', 'ExampleController@example');
Route::get('/', 'ExampleController@apiExample');
Route::post('/', 'ExampleController@apiExample');
/******************************************************************************************/