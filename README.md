# RaintplFramework - MVC
It is a simple framework that using ***[RainTpl](https://github.com/feulf/raintpl)*** with mvc pattern.
## Notes
1. No need to edit **index.php** and files under **sfw** folder
2. Do not push **initialize.php** and **.env** to github
---
## How to import

---
## initailize
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

    **NOTE:** You should change the **MODE** to **production** when you publish the website
- **debug** mode will print the all exceptions
---
## Routes
- All routes are defined in **console/app/routes/web.php** and **console/app/routes/api.php**
- Remove example data from these files
- **console/app/routes/api.php** file used only for **API calls**. 
    - All calls starts with ***console/api/*** will treated as **API call**.
    - eg: http://localhost:8888/RainTPLDemo/console/api/exampleTest/1/io/name for *Route::get('exampleTest/{id}/io/{name}', 'ExampleController@example');*
- You can define routes for **get**, **post**, **put**, **delete**, and **patch**
- Define a route with external controller (***Other than in console/app/controllers***)
    - You should pass **controllerFolderPath** as ***3rd***  argument while calling ***get,put,post,delete or patch***
    - You should set the **namesapce** of the new controller
    - eg: Route::get('createModel/{model}/{table}', 'ModelController@create','app/sfw')->setNameSpace("\SFW");
---



    
