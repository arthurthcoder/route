<?php
define("DS", DIRECTORY_SEPARATOR);
define("DOMAIN", "https://localhost/basecode/route/examples/");

require_once dirname(__DIR__).DS."vendor".DS."autoload.php";
require_once dirname(__DIR__).DS."examples".DS."Controllers".DS."UserController.php";

use BaseCode\Route\Route;

$route = new Route(DOMAIN, "::");

$route->get("/", function(){
    echo "<h1>Hello World :)</h1>";
}, "site.home");

$route->namespace("App\\Controllers\\Admin")->group("admin");

$route->get("/login", "UserController::login", "site.login");
$route->post("/login-auth", "UserController::auth", "site.auth");
$route->get("/product/{user_id}/{id}/details", "UserController::product", "site.product");

$route->execute();
