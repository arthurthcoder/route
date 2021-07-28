<?php
namespace BaseCode\Route;

use Exception;

Abstract Class Routing {
    /** @var array */
    const CONFIG = [
        "separator" => ":",
        "allowed" => [
            "GET",
            "POST",
            "PUT",
            "PATCH",
            "DELETE"
        ]
    ];

    /** @var string */
    protected $domain;

    /** @var string */
    private $namespace;

    /** @var string */
    private $separator;

    /** @var array */
    private $router;

    /** @var string */
    protected $group;
    
    /** @var array */
    protected $errors;

    /** @var bool */
    private $debug;


    /**
     * @param string|null $domain
     * @param string|null $namespace
     * @param string|null $separator
     */
    public function __construct(
        ?string $domain,
        ?string $namespace,
        ?string $separator
    ) {
        $this->debug(false);

        if (!$domain) {
            $this->error("Nenhum dominio foi definido para route!");
        }

        $this->domain = $this->trimBar($domain);
        $this->namespace($namespace);
        $this->separator = ($separator ?: self::CONFIG["separator"]);
    }

    /**
     * @param string|null $group
     * @return Routing
     */
    public function group(?string $group): Routing
    {
        $this->group = (empty($group) ? null : $this->trimBar($group));
        return $this;
    }

    /**
     * @param string|null $namespace
     * @return Routing
     */
    public function namespace(?string $namespace): Routing 
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * @param string $method
     * @param string $route
     * @param mixed $action
     * @param string|null $name
     */
    protected function add(string $method, string $route, $action, string $name = null)
    {
        if (!in_array($method, self::CONFIG["allowed"])) {
            $this->error("Método {$method} não permitido!");
        }

        if (!is_array($this->router)) {
            $this->router = [];
        }


        if (!isset($this->router[$method])) {
            $this->router[$method] = [];
        }

        $route = $this->trimBar($route);

        if ($this->group) {
            $route = $this->trimBar("{$this->group}/{$route}");
        }

        array_push($this->router[$method], [
            "route" => $route,
            "action" => $action,
            "name" => $name
        ]);
    }

    /**
     * @param string $url
     * @return string
     */
    protected function trimBar(string $url): string
    {
        if ($url == "/") {
            return $url;
        }
        return preg_replace("~^/*|/*$~", "", $url);
    }


    /**
     * @param string $name
     * @return array
     */
    private function request(string $name = null): array
    {
        $name = ($name ?: "route");
        $route = filter_input(INPUT_GET, $name, FILTER_SANITIZE_STRING);
        $route = $this->trimBar(($route ?: "/"));

        if (!$_POST) {
            return [
                "route" => $route,
                "method" => "GET"
            ];
        }

        $method = filter_input(INPUT_POST, "_method_", FILTER_SANITIZE_STRING);

        if (!$method) {
            return [
                "route" => $route,
                "method" => "POST"
            ];
        }

        if (in_array($method, self::CONFIG["allowed"])) {
            return [
                "route" => $route,
                "method" => $method
            ];
        }

        return [
            "route" => $route,
            "method" => "GET"
        ];
    }

    /**
     * @param string $find
     * @param string|null $method
     * @param string $option
     * @param array $data
     * @return array|null
     */
    protected function find(
        string $find,
        ?string $method = null,
        string $option = "route",
        array $data = []
    ):?array {

        if (!in_array($option, ["route", "name"])) {
            $this->error("A option {$option} passado para find e inválida!");
        }

        $routeAll = (isset($this->router[$method]) ? [$this->router[$method]] : $this->router);

        if (empty($routeAll)) {
            return null;
        }

        while ($routeAll) {

            $routeMethod = array_shift($routeAll);
            while ($routeMethod) {
                $route = array_shift($routeMethod);

                $params = preg_match_all(
                    "~\{([a-zA-Z][a-zA-Z0-9-_]*[a-zA-Z0-9]+)\}~",
                    $route["route"],
                    $match,
                    PREG_PATTERN_ORDER
                );

                if ($option == "route") {

                    // $params == false | para não aceitar rota /usuario/{id}
                    if ($find == $route[$option] && $params == false) {
                        $routeMethod = false;
                        continue;
                    }

                    if ($params !== false) {
                        $diff = array_diff(explode("/", $find), explode("/", $route[$option]));
                        $replace = str_replace($match[0], $diff, $route[$option]);

                        if ($find == $replace) {
                            $route["route"] = $find;
                            $route["data"] = array_combine(end($match), $diff);
                            $routeMethod = false;
                            continue;
                        }
                    }

                }// if == route

                if ($option == "name") {

                    if ($find == $route[$option]) {
                        
                        if ($params == false) {
                            $routeMethod = false;
                            continue;
                        }

                        $flip = array_flip(end($match));

                        $diff1 = array_diff_key($flip, $data);
                        $diff2 = array_diff_key($data, $flip);

                        if (empty($diff1) && empty($diff2)) {
                            $route["route"] = str_replace($match[0], $data, $route["route"]);
                            $route["data"] = $data;
                            $routeMethod = false;
                            continue;
                        }
                    }

                }// if == name

                $route = null;
            }// while

            if (!empty($route)) {
                $routeAll = false;
            }

        }// while

        return $route;
    }

    /**
     * @param array $route
     */
    private function action(array $route)
    {
        $data = (isset($route["data"]) ? $route["data"] : []);

        if (is_callable($route["action"])) {
            call_user_func($route["action"], $data);
        }else{
            $explode = explode(self::CONFIG["separator"], $route["action"]);
            $controller = (isset($explode[0]) ? str_replace("/", "\\", $explode[0]) : null);
            $action = (isset($explode[1]) ? $explode[1] : null);

            if (empty($controller) || empty($action)) {
                $this->error("A string de ação {$route["action"]} é inválida!");
            }

            if (!empty($this->namespace)) {
                $controller = "{$this->namespace}\\{$controller}";
            }

            if (!class_exists($controller)) {
                $this->error("O controller {$controller} não existe!");
            }

            if (!method_exists($controller, $action)) {
                $this->error("O metodo {$action} do controller não existe!");
            }

            (new $controller($this))->$action($data);
        }
    }

    /**
     * @param string $request
     */
    public function execute(string $request = null)
    {
        $request = $this->request($request);

        $route = $this->find($request["route"], $request["method"], "route");

        if (empty($route)) {
            $this->error("A rota {$request["route"]} não foi encontrada!");
        }

        $this->action($route);
    }

    /**
     * @param string $error
     */
    protected function error(string $error)
    {
        $this->errors[] = $error;
        $this->printer();
    }

    /**
     * @param bool $bool
     */
    public function debug(bool $bool)
    {
        if ($bool === true) {
            $this->debug = true;
            $this->printer();
            return;
        }
        $this->debug = false;
    }


    private function printer()
    {
        if ($this->debug && $this->errors) {
            print_r($this->errors);
            exit;
        }
    }

}

?>