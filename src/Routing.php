<?php
namespace BaseCode\Route;

use Exception;

Abstract Class Routing {
    /** @var array CONFIG */
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

    /** @var string $domain */
    protected $domain;

    /** @var string $namespace */
    private $namespace;

    /** @var string $separator */
    private $separator;

    /** @var array $router */
    private $router;
    
    /** @var array $errors */
    protected $errors;


    /**
     * __construct
     * @param string|null $domain
     * @param string|null $namespace
     * @param string|null $separator
     */
    public function __construct(
        ?string $domain,
        ?string $namespace,
        ?string $separator
    ) {
        try {
            
            $this->domain = filter_var($domain, FILTER_SANITIZE_STRING);

            if (empty($this->domain)) {
                throw new Exception("Nenhum dominio foi definido para route!");
            }

            $this->domain = $this->trimBar($this->domain);

            $this->namespace($namespace);

            $this->separator = filter_var($separator, FILTER_SANITIZE_STRING);
            $this->separator = ($this->separator ?: self::CONFIG["separator"]);

        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
    }

    /**
     * namespace
     * @param string|null $namespace
     */
    public function namespace(?string $namespace = null) 
    {
        $this->namespace = filter_var($namespace, FILTER_SANITIZE_STRING);
    }

    /**
     * add
     * @param string $method
     * @param string $route
     * @param mixed $action
     * @param string|null $name
     */
    protected function add(string $method, string $route, $action, ?string $name)
    {
        try {
            if (!in_array($method, self::CONFIG["allowed"])) {
                throw new Exception("Método {$method} não permitido!");
            }

            if (!is_array($this->router)) {
                $this->router = [];
            }


            if (!isset($this->router[$method])) {
                $this->router[$method] = [];
            }

            array_push($this->router[$method], [
                "route" => $this->trimBar($route),
                "action" => $action,
                "name" => $name
            ]);

        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
    }

    /**
     * trimBar
     * @param string $url
     * @return string
     */
    protected function trimBar(string $url): string
    {
        if ($url == "/") {
            return $url;
        }
        return preg_replace("~^/|/$~", "", $url);
    }


    /**
     * request
     * @param string|null $name
     * @return array
     */
    private function request(?string $name = null): array
    {
        $name = ($name ?: "route");
        $route = filter_input(INPUT_GET, $name, FILTER_SANITIZE_STRING);
        $route = $this->trimBar(($route ?: "/"));

        if (empty($_POST)) {
            return [
                "route" => $route,
                "method" => "GET"
            ];
        }

        $method = filter_input(INPUT_POST, "_method_", FILTER_SANITIZE_STRING);

        if (empty($method)) {
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
     * find
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
        try {

            if (!in_array($option, ["route", "name"])) {
                throw new Exception("A option {$option} passado para find e inválida!");
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

        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return null;
        }
    }

    /**
     * action
     * @param array $route
     */
    private function action(array $route)
    {
        try {
            $data = (isset($route["data"]) ? $route["data"] : []);

            if (is_callable($route["action"])) {
                call_user_func($route["action"], $data);
            }else{
                $explode = explode(self::CONFIG["separator"], $route["action"]);
                $controller = (isset($explode[0]) ? str_replace("/", "\\", $explode[0]) : null);
                $action = (isset($explode[1]) ? $explode[1] : null);

                if (empty($controller) || empty($action)) {
                    throw new Exception("A string de ação {$route["action"]} é inválida!");
                }

                if (!empty($this->namespace)) {
                    $controller = "{$this->namespace}\\{$controller}";
                }

                if (!class_exists($controller)) {
                    throw new Exception("O controller {$controller} não existe!");
                }

                if (!method_exists($controller, $action)) {
                    throw new Exception("O metodo {$action} do controller não existe!");
                }

                (new $controller($this))->$action($data);
            }

        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
    }

    /**
     * execute
     * @param string|null $request
     */
    public function execute(?string $request = null)
    {
        try {
            $request = $this->request($request);
    
            $route = $this->find($request["route"], $request["method"], "route");

            if (!empty($this->error())) {
                return;
            }

            if (empty($route)) {
                throw new Exception("A rota {$request["route"]} não foi encontrada!");
            }

            $this->action($route);

        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
    }

    /**
     * error
     * @return array|null
     */
    public function error(): ?array
    {
        return $this->errors;
    }
}

?>