<?php
    define("DS", DIRECTORY_SEPARATOR);

    require_once dirname(__DIR__).DS."vendor".DS."autoload.php";
    require_once dirname(__DIR__).DS."examples".DS."Controllers".DS."UserController.php";

    define("DOMAIN", "https://localhost/components/Lib/Route/examples");

    // $route = new BaseCode\Route\Route(DOMAIN, "Controllers", ":");
    $route = new BaseCode\Route\Route(DOMAIN);

    /* METHODS ALLOWED
        $route->get(string:route, string|function:action, string:name_route);
        $route->post(string:route, string|function:action, string:name_route);
        $route->put(string:route, string|function:action, string:name_route);
        $route->patch(string:route, string|function:action, string:name_route);
        $route->delete(string:route, string|function:action, string:name_route);
    */

    $route->get("/", function($data) use ($route) {
        echo "<h1>HOME PAGE</h1><br><a href=\"{$route->route('user.login')}\">Login</a>";
    }, "home");

    $route->get("/{name}", function($data) {
        echo "<h1>Olá {$data['name']}</h1>";
    }, "hello");
    
    $route->get("usuario/login", "userController:login", "user.login");

    $route->execute();

    print_r($route->error());

?>