<?php
namespace BaseCode\Route;

Class Route extends Routing {

    /**
     * @param string $domain
     * @param string $namespace
     * @param string $separator
     */
    public function __construct(
        string $domain = null,
        ?string $namespace = null,
        string $separator = null
    ) {
        parent::__construct($domain, $namespace, $separator);
    }

    /**
     * @param string $route
     * @param mixed $action
     * @param string|null $name
     * @return Route
     */
    public function get(string $route, $action, string $name = null): Route
    {
        $this->add("GET", $route, $action, $name);
        return $this;
    }

    /**
     * @param string $route
     * @param mixed $action
     * @param string|null $name
     * @return Route
     */
    public function post(string $route, $action, string $name = null): Route
    {
        $this->add("POST", $route, $action, $name);
        return $this;
    }

    /**
     * @param string $route
     * @param mixed $action
     * @param string|null $name
     * @return Route
     */
    public function put(string $route, $action, string $name = null): Route
    {
        $this->add("PUT", $route, $action, $name);
        return $this;
    }

    /**
     * @param string $route
     * @param mixed $action
     * @param string|null $name
     * @return Route
     */
    public function patch(string $route, $action, string $name = null): Route
    {
        $this->add("PATCH", $route, $action, $name);
        return $this;
    }

    /**
     * @param string $route
     * @param mixed $action
     * @param string|null $name
     * @return Route
     */
    public function delete(string $route, $action, string $name = null): Route
    {
        $this->add("DELETE", $route, $action, $name);
        return $this;
    }

    /**
     * @param string $name
     * @param array $data
     * @param string|null $method
     * @return string|null
     */
    public function route(string $name, array $data = [], string $method = null): ?string
    {
        $route = $this->find($name, $method, "name", $data);
        return (empty($route) ? null : "{$this->domain}/{$route['route']}");
    }

}

?>