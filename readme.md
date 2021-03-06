# **Route**

![route license](https://img.shields.io/github/license/arthurthcoder/route?color=%2332C754&logo=MIT)
![GitHub tag (latest by date)](https://img.shields.io/github/v/tag/arthurthcoder/route)

### What's the route ?

Summary: Route is a standalone component for creating routes in mvc systems.

## Features

- Creating quick routes.
- Creating routes with dynamic parameters.
- Creating routes of type [ get | post | put | patch | delete ].
- Default route creation.

## Getting Started

### Installation

You can install the route in your project with composer.

Just run the command below on your terminal:

```bash
composer require basecode/route
```
or in your composer.json require:

```bash
"basecode/route": "2.1.*"
```

## Usage

After installing the route, it is very easy to use it, just instantiate a route object in your project's index and start creating your routes.

### Recommendation for using the route

The recommendation is to have a .htaccess file that points the URI as parameter GET[route] to the index of your project, where the route will be.

Example **.htaccess** file:

```apacheconf
RewriteEngine On
Options -Indexes

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?route=$1
```

or you can pass the variable that receives the route as a parameter to the method execute, see the example below.

Example **index.php** file:

```php
define("DS", DIRECTORY_SEPARATOR);
define("DOMAIN", "https://localhost/project");

require_once dirname(__DIR__).DS."vendor".DS."autoload.php";

use BaseCode\Route\Route;

/*
    domain_url [string] [required]
    separator_controller_method [string] [optional] [default] = ":"

    $route = new Route(domain_url, separator_controller_method);

    # EXAMPLE
    $route = new Route(DOMAIN, ":");

*/

$route = new Route(DOMAIN);

/*
    route [string] [required]
    route_action [string|function] [required]
    route_name [string] [optional]

    $route->get(route, route_action, route_name);
    $route->post(route, route_action, route_name);
    $route->put(route, route_action, route_name);
    $route->delete(route, route_action, route_name);
    
    action [string|function]
    $route->standard(action);


    # EXAMPLE
    $route->get("admin/login", function() {
        echo "<h1>ADMIN LOGIN</h1>";
    }, "admin.login");

*/

$route->get("/", function() {
    echo "<h1>HOME PAGE</h1>";
}, "home");

$route->namespace("Controllers\\Admin")->group("admin");

$route->get("/", "Controller:login", "admin.login");

// get route to namespace: Controllers\Admin\Controller - method: login

$route->execute();
/*
    or
    $route->execute("route"); // route is the name received by $_GET
*/
```

### Creating standard route

The standard route is used when the route requested via the url is not found.

Example **standard** route:

```php
$route = new Route(DOMAIN);

$route->get("/", function() {
    echo "<h1>HOME PAGE</h1>";
}, "home");

$route->standard(function($error) {
    http_response_code($error["code"]);
    echo "<h1>ERROR: ".$error["message"]."</h1>";
});

$route->execute();
```

### Using the route method

The route method returns the route with the specified name or null if it is not found.

Example **route** method:

```php
$route = new Route(DOMAIN);

$route->get("/", function() use ($route) {

    $link = $route->route("landing.page");
    echo "<h1>HOME PAGE</h1><br><a href=\"{$link}\">Landing</a>";

}, "home");

$route->get("landing", function() use ($route) {

        $link = $route->route("home");
        echo "<h1>LANDING PAGE</h1><br><a href=\"{$link}\">Back Home</a>";

}, "landing.page");

$route->execute();
```

### Passing parameters to the route

It is possible to define parameters for the routes, thus allowing to receive dynamic values.

Example **parameters** for route:

```php
$route = new Route(DOMAIN);

$route->get("hello/{name}", function($data) {

    $name = $data["name"];
    echo "<h1>HELLO {$name}</h1>";

}, "hello.page");

$route->execute();
```

### Get url of current route

It is possible to get the url of the current route using the (current) method

Example of **current route**:

```php
$route = new Route(DOMAIN);

$route->get("product/{id}", function() use ($route) {

    $current = $route->current();
    echo "<p>ROUTE: {$current}</p>";

}, "product.page");

$route->execute();
```

### Route redirection

It is possible to redirect routes using the (redirect) method.

Example of **redirect** :

```php
$route = new Route(DOMAIN);

// REDIRECT OPTIONS

/* USING NAME

    ROUTES EXAMPLES (
        $route->get("/home", route_action, "page.home");
        $route->get("/product/{id}", route_action, "page.product");
    )

    $route->redirect("page.home"); // redirect to domain/home
    $route->redirect("page.product", ["id" => 10]); // redirect to domain/product/10
*/

/* USING URL
    $route->redirect("https://www.google.com"); // redirect to passed URL
*/

$route->group("admin");

$route->get("/login", function() {

    echo "<h1>ADMIN LOGIN</h1>";

}, "admin.login");

$route->get("/home", function() use ($route) {

    if (!isset($_SESSION["ADMIN_USER"])) {
        $route->redirect("admin.login");
    }

    echo "<h1>ADMIN HOME</h1>";

}, "admin.home");

$route->execute();
```

> To be continued...