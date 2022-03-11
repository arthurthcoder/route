<?php
namespace BaseCode\Route;

use stdClass;
use Exception;
use BaseCode\Route\Url\Url;
use BaseCode\Route\Http\Http;

/**
 * class Router
 * @package BaseCode\Route
 */
abstract class Router
{
    /** @var string */
    private $domain;
    
    /** @var string */
    private $actionSeparator;

    /** @var array */
    private $routes;

    /** @var string */
    private $currentRoute;

    /** @var string|null */
    private $namespace;

    /** @var string|null */
    private $group;

    /** @var string|callable */
    private $standard;

    /** @var array */
    private $error;

    /**
     * @param string $domain
     * @param string $separator
     */
    public function __construct(string $domain = "/", string $separator = ":")
    {
        $this->domain = Url::trim($domain) ?: "/";
        $this->actionSeparator = $separator;
    }


    /**
     * @param string|null $namespace
     * @return Router
     */
    public function namespace(?string $namespace): Router
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * @param string|null $group
     * @return Router
     */
    public function group(?string $group): Router
    {
        $this->group = Url::trim($group ?: "");
        return $this;
    }


    /**
     * @param string|null $path
     * @return string|null
     */
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


    /**
     * @param string $method
     * @param string $route
     * @param $action
     * @param string|null $name
     * @return Router
     */
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

    /**
     * @param string|null $method
     * @param bool $encapsulate
     * @return array
     */
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


    /**
     * @param string $search
     * @param array $params
     * @param bool $name
     * @param string|null $method
     * @return stdClass|null
     */
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

    /**
     * @param stdClass $route
     * @param string $search
     * @param array $params
     * @param bool $name
     * @return stdClass|null
     */
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

    /**
     * @param string $name
     * @param array $params
     * @return string|null
     */
    public function route(string $name, array $params = []): ?string
    {
        $route = $this->search($name, $params);

        if ($route) {
            return $this->domain($route->route);
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function current(): ?string
    {
        return $this->currentRoute;
    }

    /**
     * @param $action
     * @param array $params
     */
    private function action($action, array $params)
    {
        try {
            if (!is_string($action) && is_callable($action)) {
                return call_user_func($action, $params);
            }
    
            $action = explode($this->actionSeparator, $action);

            if (count($action) != 2) {
                $action = implode($this->actionSeparator, $action);
                throw new Exception("String action invalid: {$action}", 404);
            }

            $class = $action[0];

            if (!class_exists($class)) {
                throw new Exception("Class does not exists: {$class}", 404);
            }

            $method = $action[1];

            if (!method_exists($class, $method)) {
                throw new Exception("The method: {$method} of class: {$class} does not exists", 404);
            }

            return (new $class($this))->$method($params);

        }catch(Exception $e) {
            $this->error = [
                "message" => $e->getMessage(),
                "code" => $e->getCode()
            ];
        }
    }

    /**
     * @param string $route
     */
    public function execute(string $route = "route", bool $spoofing = false): void
    {
        $route = Url::trim(Http::get($route, "/")) ?: "/";
        $this->currentRoute = $this->domain($route);
        $route = $this->search($route, [], false, Http::method($spoofing));
        
        if ($route) {
            $this->action($route->action, $route->params);

            if ($this->error && $this->standard) {
                $this->action($this->standard, $this->error);
            }

            return;
        }

        $this->error = ["message" => "Route not found", "code" => 404];

        if ($this->standard) {
            $this->action($this->standard, $this->error);
        }
    }

    /**
     * @param $standard
     * @return Router
     */
    public function standard($standard): Router
    {
        if (is_string($standard) || is_callable($standard)) {
            if (is_string($standard) && $this->namespace) {
                $standard = "{$this->namespace}\\{$standard}";
            }
            $this->standard = $standard;
        }

        return $this;
    }


    /**
     * @param string $redirect
     * @param array $params
     */
    public function redirect(string $redirect, array $params = []): void
    {
        $redirect = filter_var($redirect, FILTER_VALIDATE_URL) ?: $this->route($redirect, $params);
        if ($redirect) {
            header("Location: {$redirect}");
            exit;
        }
    }

    /**
     * @return array|null
     */
    public function error(): ?array
    {
        return $this->error;
    }
}