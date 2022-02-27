<?php
namespace BaseCode\Route;

use stdClass;

Abstract Class Router
{
    private $domain;
    private $separatorAction;

    private $routes;
    private $namespace;
    private $group;

    public function __construct(string $domain = "/", string $separator = ":")
    {
        $this->domain = Url::trim($domain) ?: "/";
        $this->separatorAction = $separator;
    }

    public function namespace(string $namespace): Router
    {
        $this->namespace = $namespace;
        return $this;
    }

    public function group(string $group = null): Router
    {
        $this->group = Url::trim($group ?: "");
        return $this;
    }

    public function domain(string $path = null): ?string
    {
        if ($this->domain) {
            $path = Url::trim($path ?: "");

            if ($this->domain == "/") {
                return "/{$path}";
            }

            return Url::trim("{$this->domain}/{$path}");
        }

        return null;
    }

    protected function addRoute(string $method, string $route, $action, ?string $name): Router
    {
        $this->routes = $this->routes ?: [];

        if (!isset($this->routes[$method])) {
            $this->routes[$method] = [];
        }

        $newRoute = new stdClass();
        $newRoute->route = Url::trim($route) ?: "/";

        if ($this->group) {
            $newRoute->route = Url::trim("{$this->group}/{$newRoute->route}");
        }

        $newRoute->params = Url::params($newRoute->route);
        $newRoute->action = $action;

        if ($this->namespace && is_string($newRoute->action)) {
            $newRoute->action = "{$this->namespace}\\{$newRoute->action}";
        }

        $newRoute->name = $name;

        array_push($this->routes[$method], $newRoute);

        return $this;
    }

    private function search(
        string $search,
        array $params = [],
        bool $name = true,
        string $method = null
    ): ?string {
        $list = $this->routes;

        if ($method) {
            if (isset($this->routes[$method])) {
                $list = [
                    $method => $this->routes[$method]
                ];
            }
        }

        foreach ($list as $routes) {
            foreach ($routes as $route) {
                $route = $this->engine($route, $search, $params, $name);

                if ($route) {
                    return $route;
                }
            }
        }

        return null;
    }

    private function engine(stdClass $route, string $search, array $params, bool $name): ?string
    {
        if ($name) {
            if ($route->name == $search) {
                if (count($route->params) == count($params)) {
                    if (count($route->params) == 0) {
                        return $route->route;
                    }
    
                    $route = str_replace($route->params, $params, $route->route);
                    if (empty(Url::params($route))) {
                        return $route;
                    }
                }
            }

            return null;
        }

    }

    private function routes(string $method = null): array
    {
        if ($method) {
            return isset($this->routes[$method]) ? $this->routes[$method] : [];
        }

        return $this->routes ?: [];
    }

    public function route(string $name, array $params = []): ?string
    {
        $route = $this->search($name, $params);

        if ($route) {
            return $this->domain($route);
        }

        return null;
    }

    public function execute(string $get = "route")
    {

    }

    public function test()
    {
        echo "<pre>";
        print_r($this->routes);
        echo "<pre>";
    }
}