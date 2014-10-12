<?php

namespace Simplon\Frontend;

class Router
{
    /** @var  string */
    protected static $route;

    /** @var  string */
    protected static $request;

    /**
     * @param null $route
     *
     * @return bool
     */
    protected static function setup($route = null)
    {
        if ($route === null)
        {
            $route = $_SERVER['PATH_INFO'];
        }

        // set route
        self::$route = rtrim($route, '/');

        // set request method
        self::$request = strtoupper($_SERVER['REQUEST_METHOD']);

        return true;
    }

    /**
     * @param $controller
     * @param array $params
     *
     * @return string
     */
    protected static function callControllerAction($controller, array $params = [])
    {
        list($controller, $method) = explode('::', $controller);

        return (string)call_user_func_array([(new $controller), $method], $params);
    }

    /**
     * @param $route
     *
     * @return string
     */
    protected static function show404($route)
    {
        header('HTTP/1.1 404 Not Found');

        return self::callControllerAction($route);
    }

    /**
     * @param array $routes
     * @param null $requestedRoute
     *
     * @return string
     */
    public static function observe(array $routes, $requestedRoute = null)
    {
        self::setup($requestedRoute);

        // --------------------------------------

        foreach ($routes['routes'] as $route)
        {
            if (preg_match_all('#' . $route['pattern'] . '/*#i', self::$route, $match, PREG_SET_ORDER))
            {
                // handle request method restrictions
                if (isset($route['request']) && strpos(strtoupper($route['request']), self::$request) === false)
                {
                    continue;
                }

                // prepare params
                $params = [];

                if (isset($match[0][1]))
                {
                    // remove matched string
                    unset($match[0][0]);

                    // set params
                    $params = $match[0];
                }

                // run controller
                return self::callControllerAction($route['controller'], $params);
            }
        }

        // --------------------------------------

        return self::show404($routes['custom']['404']);
    }
}