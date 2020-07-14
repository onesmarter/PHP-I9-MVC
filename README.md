# RainTpl - MVC
It is a simple framework that using ***[RainTpl](https://github.com/feulf/raintpl)*** with mvc pattern.
## Notes
1. No need to edit **index.php** and files under **sfw** folder
2. Do not push **initialize.php** and **.env** to github
---
## How to import
- **Fork** the [RainTpl-MVC](https://github.com/josephpaul0487/RainTpl-MVC.git) repository to your github account.  *Learn* [Fork a repo](https://docs.github.com/en/github/getting-started-with-github/fork-a-repo)
---
## Initailize
**console/config/inialize-example.php**
                    *AND*
**console/config/config.php**

1. Create a new file **initialize.php** in  **console/config/** folder
2. Copy all data from **inialize-example.php** to **initialize.php**
3. Change the defined ***DB*** and ***PATH*** variable details
4. Change the **MODE** to any of *development , production , debug*
5. You can add or edit other configurations in **console/config/config.php** 
6. Do not delete the **HelperFunctions.php** import statement in  **console/config/config.php** . ***However you can edit the path.***

    *You can replace **step 1 and 2** with* 
- rename the **inialize-example.php** to **initialize.php**

##### ```NOTE```
    
- You should change the **MODE** to **production** when you publish the website
- debug mode will print the all exceptions
    
---
## Routes
- All routes are defined in **console/app/routes/web.php** and **console/app/routes/api.php**
- Remove example data from these files
- **console/app/routes/api.php** file used only for **API calls**. 
    - All calls starts with ***console/api/*** will treated as **API call**.
    ```
    http://localhost:8888/RainTPLDemo/console/api/exampleTest/1/io/name for 
    
    Route::get('exampleTest/{id}/io/{name}', 'ExampleController@example');
    ```
- You can define routes for **get**, **post**, **put**, **delete**, and **patch**
- Define a route with external controller (***Other than in console/app/controllers***)
    - You should pass **controllerFolderPath** as ***3rd***  argument while calling ***get,put,post,delete or patch***
    - You should set the **namesapce** of the new controller
    ```
    Route::get('createModel/{model}/{table}', 'ModelController@create','app/sfw')->setNameSpace("\SFW");
    ```
##### MiddleWares
- You can use middlewares for authentication for a group of routes
- Create Middleware php file in **console/app/middlewares** folder
- Return anything if you want to avoid original route call to the controller
- Create a function name **request** in the class
```sh
<?php
namespace SFW\MiddleWares;
use RainTPL;
use SFW\Request;
use SFW\Connection;
class ExampleMiddleWare {
    public function request(Request $request,Connection $connection,RainTPL $tpl) {
        if(!empty($request->data['showError'])) {
            return $tpl->draw('error/404', $return_string = true);
        }
        
    }
}
```
###### Define Middlewares
- Provide **middleware class name** as first parameter
- provide an **empty parameter function** with all routes for this middleware defined as second parameter
```sh
Route::group("ExampleMiddleWare",function () {
    Route::get('/', 'ExampleController@example');
    Route::post('/', 'ExampleController@example');
});
```
- You can define a middleware **for a single Route** as following way
```sh
Route::get('/{ showError  }', 'ExampleController@example')->setMiddleWare("ExampleMiddleWare");
```

---



    
