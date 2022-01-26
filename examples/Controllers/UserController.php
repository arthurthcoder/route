<?php
namespace Controllers;

Class UserController
{
    private $route;

    public function __construct($route)
    {
        $this->route = $route;
        $this->route->url('css', 'assets/css');
    }

    public function login()
    {
        $current = $this->route->current();
        $css = $this->route->url('css');
        echo "<h1>LOGIN PAGE: {$current}</h1>";
        echo "<h2>CSS: {$css}</h2>";
    }

}
?>