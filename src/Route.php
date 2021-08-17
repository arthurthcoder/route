<?php
namespace BaseCode\Route;

/**
 * Class Route
 * @package BaseCode\Route
 */
Class Route extends Routing
{

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
     * @return string|null
     */
    public function current(): ?string
    {
        if (empty($this->current)) {
            return null;
        }

        if ($this->current == "/") {
            return $this->domain;
        }

        return "{$this->domain}/{$this->current}";
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
        return (empty($route) ? null : $this->trimBar("{$this->domain}/{$route['route']}"));
    }

    /**
     * @param string $name
     * @param array $data
     * @param string $method
     */
    public function redirect(string $name, array $data = [], string $method = null)
    {
        $route = $this->route($name, $data, $method);
        if (filter_var($route, FILTER_VALIDATE_URL)) {
            header("Location: {$route}");
            exit;
        }
    }

    /**
     * @param string $name
     * @param string $route
     * @return string|null
     */
    public function url(string $name, string $route = null): ?string
    {
        return $this->custom($name, $route);
    }

}

?>