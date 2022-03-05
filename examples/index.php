<?php
define("DS", DIRECTORY_SEPARATOR);
define("DOMAIN", "https://localhost/basecode/route/examples/");

require_once dirname(__DIR__).DS."vendor".DS."autoload.php";
require_once dirname(__DIR__).DS."examples".DS."Controllers".DS."UserController.php";

use BaseCode\Route\Route;

$route = new Route(DOMAIN, "::");

$route->get("/", function() use ($route){
    echo "<h1>Hello World :)</h1><br>";
    echo "<a href=\"{$route->route("site.login")}\">Login</a>";
}, "site.home");

$route->get("/redir", function() use ($route){
    $route->redirect("site.hello", ["name" => "coder"]);
}, "site.redir");


$route->get("/{name}", function($data){
    $name = $data["name"];
    echo "<h1>Hello {$name}</h1>";
}, "site.hello");

$route->get("/produto/{id}/detalhes", function($data){
    $id = $data["id"];
    echo "<h1>Detalhes do producto: #{$id}</h1>";
}, "site.product");

$route->namespace("Controllers")->group("admin");

$route->get("/login", "UserController::login", "site.login");

$route->namespace(null)->standard(function($error){
    $message = $error["message"];
    echo "<h1>{$message}</h1>";
});

$route->execute();

// if ($route->error()) {
//     print_r($route->error());
// }