
<?php
require __DIR__.'/vendor/autoload.php';
include('config/initialize.php');

use \SFW\InitController; 

startSession();

if(IS_FOR_API) {
    include 'app/routes/api.php';
} else {
    include 'app/sfw/routes.php';
    include 'app/routes/web.php';
}

$init = new InitController($allParams);
$response = $init->init($callingUrl);



if (empty(trim($response))) {
    if(IS_FOR_JSON_OUTPUT===true) {
        echo json_encode(["status"=>0,"msg"=>"Api Not Found"]);
        exit();
    }
    echo $tpl->draw('error/404', $return_string = true);
} else {
    echo $response;
}
exit();
// throw new \Exception("Error Processing Request", 1);






