<?php
namespace BaseCode\Route\Url;

/**
 * class Url
 * @package BaseCode\Route
 */
class Url
{
    /**
     * @param string $url
     * @return string
     */
    public static function trim(string $url): string
    {
        return preg_replace("/^\/+|\/+$/", "", $url);
    }

    /**
     * @param string $url
     * @return array
     */
    public static function params(string $url): array
    {
        $pattern = "/\{([a-zA-Z][a-zA-Z0-9_-]*[a-zA-Z0-9])\}/";
        preg_match_all($pattern, $url, $match);
        return array_combine($match[1], $match[0]);
    }
}