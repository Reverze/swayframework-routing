<?php

namespace Sway\Component\Route\Exception;

class RouteException extends \Exception
{
    /**
     * Throws an exception when parameter has been missed while compile route's path
     * @param string $parameterName
     * @return \Sway\Component\Route\Exception\RouteException
     */
    public static function missedParameterCompileUri(string $parameterName) : RouteException
    {
        return (new RouteException(sprintf("Parameter '%s' has been missed while compiling route's path", $parameterName)));
    }
    
    /**
     * Throws an exception when parameter's value is empty while compile route's path
     * @param string $parameterName
     * @return \Sway\Component\Route\Exception\RouteException
     */
    public static function emptyParameterValueCompileUri(string $parameterName) : RouteException
    {
        return (new RouteException(sprintf("Parameter '%s' value cannot be empty!", $parameterName)));
    }
    
    /**
     * Throws an exception when directive execute doesnt pass parameter value
     * @param string $parameterName
     * @return \Sway\Component\Route\Exception\RouteException
     */
    public static function parameterDirectiveFail(string $parameterName) : RouteException
    {
        return (new RouteException(sprintf("Directives executor doesnt pass parameter '%s' value", $parameterName)));
    }
}


?>
