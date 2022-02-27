<?php
namespace BaseCode\Route;

Class Url
{
    public static function trim(string $url): string
    {
        return preg_replace("/^\/+|\/+$/", "", $url);
    }

    public static function params(string $url): array
    {
        $pattern = "/\{([a-zA-Z][a-zA-Z0-9_-]*[a-zA-Z0-9])\}/";
        preg_match_all($pattern, $url, $match);
        return array_combine($match[1], $match[0]);
    }
}