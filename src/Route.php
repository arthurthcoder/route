<?php
namespace BaseCode\Route;

/**
 * class Route
 * @package BaseCode\Route
 */
class Route extends Router
{
    /**
     * @param string $route
     * @param $action
     * @param string|null $name
     * @return Route
     */
    public function get(string $route, $action, string $name = null): Route
    {
        $this->addRoute("GET", $route, $action, $name);
        return $this;
    }

    /**
     * @param string $route
     * @param $action
     * @param string|null $name
     * @return Route
     */
    public function post(string $route, $action, string $name = null): Route
    {
        $this->addRoute("POST", $route, $action, $name);
        return $this;
    }

    /**
     * @param string $route
     * @param $action
     * @param string|null $name
     * @return Route
     */
    public function put(string $route, $action, string $name = null): Route
    {
        $this->addRoute("PUT", $route, $action, $name);
        return $this;
    }

    /**
     * @param string $route
     * @param $action
     * @param string|null $name
     * @return Route
     */
    public function patch(string $route, $action, string $name = null): Route
    {
        $this->addRoute("PATCH", $route, $action, $name);
        return $this;
    }

    /**
     * @param string $route
     * @param $action
     * @param string|null $name
     * @return Route
     */
    public function delete(string $route, $action, string $name = null): Route
    {
        $this->addRoute("DELETE", $route, $action, $name);
        return $this;
    }
}