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
- You should any data from the controller. Otherwise it will show error page or json error message
    
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

## Models
- Are using only for a table
- All tables columns are converted in to **camelCase fields** in models
```html
user_id Or User_id will converted into userId
USER_ID  will converted into uSeRId
```
- It will replace all next character after the **_** to UpperCase except first character.
- You can use array access **user['userId']** OR object access **user->userId** from the model.
##### Creating a model
- **http://SERVER_ADDRESS/console/createModel/user**
- It will create a file called **UserModel** in **console/app/models directory**
- And also create a table named **tbl_users** if not exists
- You can pass preffered table name using **http://SERVER_ADDRESS/console/createModel/user/tablename**

- **Don'ts**
    1. Don't pass ['php','.php','models','model','controller','controllers','middleware','middlewares'] strings as a model name suffix.
```
ie, Don't use http://SERVER_ADDRESS/console/createModel/usermodel
```
##### Model Db functions [STATIC]
- Some parameters are optional in all functions
- **To find a model**
```
ModelName::find(primaryColumnValue); 
OR
ModelName::findOne($connection,$conditionArray,$orderBy,$ascending,$columns);
OR
ModelName::findOneByQuery($sqlQuery,$connection);
```
- **To find a list of data**
```
ModelName::findAll($connection,$conditionArray,$columns,$orderBy,$ascending,$limit,$offset);
OR
ModelName::findAllByQuery($sqlQuery,$connection);
```
- **Get data for pagination**
```
ModelName::pagination($connection,$conditionArray,$columns,$orderBy,$ascending,$limit,$offset);
Default value for $limit is 25 and $offset is 0
```

**OUTPUT of pagination**
```
['totalCount'=>totalCount,'currentPage'=>page,'totalPages'=>totalPages,'from'=>offset,'requestedCount'=>limit,'count'=>modelsCount,'data'=>models]
```
- **Datatable pagination - see below**
##### Model Db functions [Object]
- Some parameters are optional in all functions
- **To fetch details**
```
$model->fetch();
```
- **To insert**
```
$model->insert();
```
- **To update**
```
$model->update();
```
- **To delete**
```
$model->delete();
```
- **To insertOrUpdate**
```
$model->insertOrUpdate();
```

- **To Save**
```
$model->save();
save function will either insert a new data if primary value is empty or update the model.
```

##### Model Other functions(Object)
- To get all variables as an array
    1. You can pass **$forDatabase = true** to get the keys as table column names
    2. Pass array of **$variableNames=['var1,'var2']** to get selected variables
```
$model->getModelValues($forDatabase,$variableNames);
```
    
- To get all variables that have values defined as an array
    1. You can pass **$forDatabase = true** to get the keys as table column names
    2. Pass array of **$variableNames=['var1,'var2']** to get selected variables
```
$model->getFieldsHaveValue($forDatabase,$variableNames);
```

- To get the column name of a variable
```
$model->getColumnName($variableName);
```

- To get the variable name of a column
```
$model->getFieldName($columnName);
```

## Controllers
- Default location is console/app/controller
- Define functions to use Route from the InitController
- InitController will pass three arguments to all routed functions
```
public function dashboard(Request $request,Connection $connection,RainTPL $tpl) {
    return $tpl->draw('after-login/dashboard', $return_string = true);
}
```    
##### Request 
- Contains the all data [data=>arguments passed ,headers and files array]
##### Creating a controller
- **http://SERVER_ADDRESS/console/createController/user**
- It will create a file called **UserController** in **console/app/controller directory**
- **Don'ts**
    1. Don't pass ['php','.php','models','model','controller','controllers','middleware','middlewares'] strings as a model name suffix.
```
ie, Don't use http://SERVER_ADDRESS/console/createController/userController
```

## MiddleWares
- Default location is console/app/middlewares
- The Middlewares are called before calling the controller functions
- Middlewares are defined in console/app/routes/api.php OR console/app/routes/web.php
```
Route::group("AuthMiddleWare",function () {
    Route::get('verify', 'DashBoardController@dashboard');
});
```
- The Middleware class should contain a function named request
- InitController will pass three arguments to request function [Request $request,Connection $connection,RainTPL $tpl]
```
public function request(Request $request,Connection $connection,RainTPL $tpl) {
        if(empty($_SESSION['user'])) {
            if(IS_FOR_JSON_OUTPUT===true) {
                return jsonResponse(null,0,"Users cannot access this section without login");
            }
            header("location:login");
            exit;
        }
    }
``` 
- **The controller function will not  call if the request function returning anything**

##### Creating a Middleware
- **http://SERVER_ADDRESS/console/createMiddleWare/auth**
- It will create a file called **AuthMiddleWare** in **console/app/middlewares directory**
- **Don'ts**
    1. Don't pass ['php','.php','models','model','controller','controllers','middleware','middlewares'] strings as a model name suffix.
```
ie, Don't use http://SERVER_ADDRESS/console/createMiddleWare/authmiddleware
```

    
## Datatable pagination

- Can use datatable auto pagination using ajax in this framework
- You can pass your own search filter
- The columns given by datatable is using for default search if the search filter is null
- All columns of the table will use for default search  if the columns given by datatable is empty or the search filter is null
**Without Conditions and search filter**
```
public function getList(Request $request,Connection $connection) {
        $data = UserModel::dataTablePagination($request->data,$connection);
}
```
**With Own Query Conditions and without search filter**
```
public function getList(Request $request,Connection $connection) {
        $builder = $connection->getQueryBuider();
        $builder = $builder->where('status','deleted','!=');
        $data = UserModel::dataTablePagination($request->data,$connection,$builder);
}
```
**Without Conditions and With search filter**
```
public function getList(Request $request,Connection $connection) {
        $data = UserModel::dataTablePagination($request->data,$connection,null,function($queryBuilder,$searchString) {
            $queryBuilder = $queryBuilder->like('name','%'.$searchString.'%');
        });
}
```
**With Conditions and  search filter**
```
public function getList(Request $request,Connection $connection) {
        $builder = $connection->getQueryBuider();
        $builder = $builder->where('status','deleted','!=');
        $data = UserModel::dataTablePagination($request->data,$connection,$builder,function($queryBuilder,$searchString) {
            $queryBuilder = $queryBuilder->like('name','%'.$searchString.'%');
        });
}
```