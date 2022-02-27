<?php
namespace BaseCode\Route;

use stdClass;
use BaseCode\Route\Url\Url;
use BaseCode\Route\Http\Http;
use Exception;

Abstract Class Router
{
    private $domain;
    private $separatorAction;

    private $routes;
    private $namespace;
    private $group;

    private $error;

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

    private function routes(string $method = null, bool $encapsulate = false): array
    {
        if ($method) {
            if (isset($this->routes[$method])) {
                return $encapsulate ? [$method => $this->routes[$method]] : $this->routes[$method]; 
            }

            return [];
        }

        return $this->routes ?: [];
    }

    private function search(
        string $search,
        array $params = [],
        bool $name = true,
        string $method = null
    ): ?stdClass {
        foreach ($this->routes($method, true) as $routes) {
            foreach ($routes as $route) {
                $route = $this->engine($route, $search, $params, $name);

                if ($route) {
                    return $route;
                }
            }
        }

        return null;
    }

    private function engine(stdClass $route, string $search, array $params, bool $name): ?stdClass
    {
        if ($name) {
            if ($route->name == $search) {
                if (count($route->params) == count($params)) {
                    if (count($route->params) == 0) {
                        return $route;
                    }

                    $params = array_filter($params, function($value){
                        return !empty(trim($value));
                    });
    
                    $replaces = $route->params;
                    $route->params = array_merge($route->params, $params);
                    $route->route = str_replace($replaces, $route->params, $route->route);
                    
                    if (empty(Url::params($route->route))) {
                        return $route;
                    }
                }
            }

            return null;
        }

        if ($route->route == $search) {
            if (Url::params($route->route)) {
                return null;
            }

            return $route;
        }
        
        $difference = array_diff(explode("/", $search), explode("/", $route->route));
        $compare = str_replace($route->params, $difference, $route->route);

        if ($compare == $search) {
            $route->params = array_combine(array_keys($route->params), $difference);
            return $route;
        }

        return null;
    }

    public function route(string $name, array $params = []): ?string
    {
        $route = $this->search($name, $params);

        if ($route) {
            return $this->domain($route->route);
        }

        return null;
    }

    private function action(?stdClass $route)
    {
        try {

            if (empty($route)) {
                throw new Exception("route_does_not_exists", 404);
            }
    
            if (is_callable($route->action)) {
                return call_user_func($route->action, $route->params);
            }
    
            $action = explode($this->separatorAction, $route->action);

            if (count($action) < 2) {
                throw new Exception("action_invalid_route( {$route->name} )", 404);
            }

            $class = $action[0];

            if (!class_exists($class)) {
                throw new Exception("class_does_not_exists_route( {$route->name} )", 404);
            }

            $method = $action[1];

            if (!method_exists($class, $method)) {
                throw new Exception("method_does_not_exists_in_class_route( {$route->name} )", 404);
            }

            return (new $class($this))->$method($route->params);

        }catch(Exception $e) {
            Http::error($e->getCode());
            $this->error = $e->getMessage();
        }
    }

    public function execute(string $get = "route")
    {
        $requested = Url::trim(Http::get($get) ?: "/") ?: "/";
        $route = $this->search($requested, [], false, Http::method());
        $this->action($route);
    }

    public function test()
    {
        echo "<pre>";
        print_r($this->route("site.product", [
            "id" => 22,
            "user_id" => 1
        ]));
        echo "<pre>";
    }
}