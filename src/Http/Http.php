<?php
namespace BaseCode\Route\Http;

/**
 * class Http
 * @package BaseCode\Route
 */
class Http
{
    /**
     * @param string|null $name
     * @param $default
     */
    public static function get(string $name = null, $default = null)
    {
        if ($name) {
            return  filter_input(INPUT_GET, $name, FILTER_DEFAULT) ?: $default;
        }

        return filter_input_array(INPUT_GET);
    }

    /**
     * @param string|null $name
     * @param $default
     */
    public static function post(string $name = null, $default = null)
    {
        if ($name) {
            return filter_input(INPUT_POST, $name, FILTER_DEFAULT) ?: $default;
        }

        return filter_input_array(INPUT_POST);
    }

    /**
     * @param bool $spoofing
     * @return string
     */
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
}