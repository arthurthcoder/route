<?php
namespace Controllers;

Class UserController
{
    private $route;

    public function __construct($route)
    {
        $this->route = $route;
    }

    public function login()
    {
        echo "<h1>LOGIN PAGE</h1><br><a href=\"{$this->route->route("site.home")}\">Inicio</a>";
    }

    public function error($data)
    {
        $message = $data["message"];
        echo "<h1>{$message}</h1>";
    }

}
?>