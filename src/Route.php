<?php
namespace BaseCode\Route;

Class Route extends Router
{
    public function get(string $route, $action, string $name = null): Route
    {
        $this->addRoute("GET", $route, $action, $name);
        return $this;
    }

    public function post(string $route, $action, string $name = null): Route
    {
        $this->addRoute("POST", $route, $action, $name);
        return $this;
    }

    public function put(string $route, $action, string $name = null): Route
    {
        $this->addRoute("PUT", $route, $action, $name);
        return $this;
    }

    public function patch(string $route, $action, string $name = null): Route
    {
        $this->addRoute("PATCH", $route, $action, $name);
        return $this;
    }

    public function delete(string $route, $action, string $name = null): Route
    {
        $this->addRoute("DELETE", $route, $action, $name);
        return $this;
    }
}