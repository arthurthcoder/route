<?php
    Class UserController {

        private $route;

        public function __construct($route)
        {
            $this->route = $route;
        }

        public function login()
        {
            echo "<h1>LOGIN PAGE</h1>";
        }

    }
?>