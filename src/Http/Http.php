<?php
namespace BaseCode\Route\Http;

class Http
{
    public static function get(string $name = null)
    {
        if ($name) {
            return filter_input(INPUT_GET, $name, FILTER_DEFAULT);
        }

        return filter_input_array(INPUT_GET);
    }

    public static function post(string $name = null)
    {
        if ($name) {
            return filter_input(INPUT_POST, $name, FILTER_DEFAULT);
        }

        return filter_input_array(INPUT_POST);
    }

    public static function method(bool $spoofing = false): string
    {
        if ($spoofing) {
            $method = self::post("_method");
            if (in_array($method, ["PUT", "PATCH", "DELETE"])) {
                return $method;
            }
        }

        $method = filter_input(INPUT_SERVER, "REQUEST_METHOD", FILTER_DEFAULT);

        if ($method) {
            return $method;
        }

        if (self::post()) {
            return "POST";
        }

        return "GET";
    }

    public static function error(int $code): void
    {
        http_response_code($code);
    }
}