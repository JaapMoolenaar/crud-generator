# Laravel 5 CRUD Generator
Laravel CRUD Generator

### Requirements
    Laravel >=5.1
    PHP >= 5.5.9

## Installation

1. Run
    ```
    composer require jaapmoolenaar.nl/crud-generator
    ```

2. Add service provider to **/config/app.php** file.
    ```php
    'providers' => [
        ...

        Appzcoder\CrudGenerator\CrudGeneratorServiceProvider::class,
    ],
    ```

3. Add service provider & aliases for **laravelcollective/html** to **/config/app.php** file.
    ```php
    'providers' => [
        ...

        Collective\Html\HtmlServiceProvider::class,
    ],

    'aliases' => [
        ...

        'Form'      => Collective\Html\FormFacade::class,
        'HTML'      => Collective\Html\HtmlFacade::class,
    ],
    ```

4. Add service provider & aliases for **laracasts/flash** to **/config/app.php** file.
    ```php
    'providers' => [
        ...

        Laracasts\Flash\FlashServiceProvider::class,
    ],

    'aliases' => [
        ...

        'Flash'     => Laracasts\Flash\Flash::class,
    ],
    ```

4. Run **composer update** if composer hasn't installed the updates already

5. Publish config file & generator template files.
    ```
    php artisan vendor:publish
    ```

    This copies the stubs to resources/crud-generator/, remember to enable them in the config ('custom_template' in crudgenerator.php) if you want to use them

Note: You should have configured database for this operation.

## Commands

#### Crud command:

```
php artisan crud:generate Posts --fields="title:string, body:text"
```

You can also easily include route, set primary key, set views directory etc through options **--route**, **--pk**, **--view-path** as belows:

```
php artisan crud:generate Posts --fields="title:string:required, body:text:required" --route=yes --pk=id --view-path="admin" --namespace=Admin --route-group=admin
```

Options:

- --fields : Fields name for the form & model.
- --route : Include Crud route to routes.php? yes or no.
- --pk : The name of the primary key.
- --view-path : The name of the view path.
- --namespace : Namespace of the controller.
- --route-group : Prefix of the route group.

-----------
-----------


#### Other commands (optional):

For controller generator:

```
php artisan crud:controller PostsController --crud-name=posts --model-name=Post --view-path="directory" --route-group=admin
```

For model generator:

```
php artisan crud:model Post --fillable="['title', 'body']"
```

For migration generator:

```
php artisan crud:migration posts --schema="title:string, body:text"
```

For view generator:

```
php artisan crud:view posts --fields="title:string, body:text" --view-path="directory" --route-group=admin
```

By default, the generator will attempt to append the crud route to your *routes.php* file. If you don't want the route added, you can use the option ```--route=no```.

After creating all resources, run migrate command. *If necessary, include the route for your crud as well.*

```
php artisan migrate
```

If you chose not to add the crud route in automatically (see above), you will need to include the route manually.
```php
Route::resource('posts', 'PostsController');
```

### Supported Field Types

These fields are supported for migration and view's form:

* string
* char
* varchar
* password
* email
* date
* datetime
* time
* timestamp
* text
* mediumtext
* longtext
* json
* jsonb
* binary
* number
* integer
* bigint
* mediumint
* tinyint
* smallint
* boolean
* decimal
* double
* float

### Custom Generator's Stub Template

You can customize the generator's stub files/templates to achieve your need.

1. Make sure you've published package's assets.
    ```
    php artisan vendor:publish
    ```

2. Turn on custom_template support on **/config/crudgenerator.php**
    ```
    'custom_template' => true,
    ```
3. From the directory **/resources/crud-generator/** you can modify or customize the stub files.


### Other configuration options
    
You're able to use another layout to extend than 'layouts.master', by using this configuration option:
(If it's anything other than the default 'layouts.master', the layout stub will not be copied)
```php
'extend_layout' => 'layouts.app',
```

If you use another section name in your layout, set it using:
```php
'section_name' => 'sub_content',
```

Set middleware to be used by the resource controller, using:
(Laravel 5.2's 'web' middleware, will be added automatically)
```php
'middleware' => 'auth',
```

##Authors

[Sohel Amin](http://www.sohelamin.com)

[Jaap Moolenaar](http://jaapmoolenaar.nl)
