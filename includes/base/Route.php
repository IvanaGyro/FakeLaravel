<?php

namespace FakeLaravel\base;

define("ROOT_DIR", "/FakeLaravel/");
class Route
{
    private static $postRoutes = [];
    private static $getRoutes = [];

    public static function boot()
    {
        require_once "route.php";
    }

    public static function route(Request $request)
    {
        $url = $_SERVER["SCRIPT_NAME"];
        if (strpos($url, ROOT_DIR) == 0) {
            $url = substr($url, strlen(ROOT_DIR));
        }
        switch ($_SERVER['REQUEST_METHOD']) {
            case "POST":
                if (!isset(self::$postRoutes[$url])) {
                    // do something
                } else {
                    $callback = self::$postRoutes[$url];
                    return $callback($request);
                }
                break;
            case "GET":
                if (!isset(self::$getRoutes[$url])) {
                    // do something
                } else {
                    $callback = self::$getRoutes[$url];
                    return $callback($request);
                }
                break;
        }
    }

    public static function post($url, $callback)
    {
        self::$postRoutes[$url] = $callback;
    }

    public static function get($url, $callback)
    {
        self::$getRoutes[$url] = $callback;
    }


}
