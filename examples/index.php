<?php
    define("DS", DIRECTORY_SEPARATOR);

    require_once dirname(__DIR__).DS."vendor".DS."autoload.php";
    require_once dirname(__DIR__).DS."examples".DS."Controllers".DS."UserController.php";

    define("DOMAIN", "https://localhost/components/Route/examples");

    // $route = new BaseCode\Route\Route(DOMAIN, "Controllers", ":");
    $route = new BaseCode\Route\Route(DOMAIN);
    $route->debug(true);

    /* METHODS ALLOWED
        $route->get(string:route, string|function:action, string:name_route);
        $route->post(string:route, string|function:action, string:name_route);
        $route->put(string:route, string|function:action, string:name_route);
        $route->patch(string:route, string|function:action, string:name_route);
        $route->delete(string:route, string|function:action, string:name_route);
    */

    $route->namespace("Controllers");

    $route->get("/", function($data) use ($route) {
        echo "<h1>HOME PAGE</h1><br><a href=\"{$route->route('user.login')}\">Login</a>";
    }, "home");

    $route->group("hello");

    $route->get("/{name}", function($data) {
        echo "<h1>Olá {$data['name']}</h1>";
    }, "hello");
    /* group hello end */


    $route->namespace(null)->group("usuario");

    $route->get("login", "userController:login", "user.login");
    /* group usuario end */

    $route->group(null);

    $route->get("landing", function($data) {
        echo "<h1>LANDING PAGE</h1>";
    }, "landing.page");

    $route->execute();

?>