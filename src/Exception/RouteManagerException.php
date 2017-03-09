<?php

namespace Sway\Component\Route\Exception;


class RouteManagerException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, \Throwable $previous = null) 
    {
        parent::__construct($message, $code, $previous);
    }
    
    /**
     * Creates an exception object when required route's parameter is missed
     * @param string $parameterName
     * @return \Sway\Component\Route\Exception\RouteManagerException
     */
    public static function requiredRouteParameterException (string $parameterName) : RouteManagerException
    {
        $routeManagerException = new RouteManagerException(
                sprintf("Route's parameter '%s' is required!", $parameterName)
        );
        return $routeManagerException;
    }
    
    /**
     * Throws an exception when controller doesn't return response
     * @return \Sway\Component\Route\Exception\RouteManagerException
     */
    public static function emptyControllerResponse() : RouteManagerException
    {
        return (new RouteManagerException("Controller must return response!"));
    }
    
    /**
     * Throws an exception when route has not been found by name
     * @param string $routeName
     * @return \Sway\Component\Route\Exception\RouteManagerException
     */
    public static function routeNotFoundByRouteName(string $routeName) : RouteManagerException
    {
        return (new RouteManagerException(sprintf("Route not found by name '%s'", $routeName)));
    }
}


?>