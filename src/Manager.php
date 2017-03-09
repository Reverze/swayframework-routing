<?php

namespace Sway\Component\Route;

use Sway\Component\Route\Exception;
use Sway\Component\Text\Stringify;
use Sway\Component\Regex\Regex;
use Sway\Component\Http\Response;

use Sway\Component\Dependency\DependencyInterface;

class Manager extends DependencyInterface
{
    /**
     * Contains Route objects
     * @var array
     */
    private $routes = array();
    
    /**
     * Httpd interface
     * @var \Sway\Component\Httpd\HttpdInterface
     */
    private $httpdInterface = null;
    
    public function __construct() 
    {
        
    }
    
    public function dependencyController()
    {
        $this->httpdInterface = $this->getDependency('httpd');
    }
    
    /**
     * Adds route 
     * @param \Sway\Component\Route\Route $route
     */
    public function addRoute(Route $route)
    {
        array_push($this->routes, $route);
    }
    
    public function registerRoutesFromArray (array $routeArray)
    {
        
        foreach ($routeArray as $routeName => $routeParameters){
            /* Gets root path of route */
            $rootPath = $routeParameters['path'] ?? null;
            
            if (!empty($rootPath)){
                $basePath = Stringify::blank();
                $controller = $routeParameters['controller'] ?? null;
                
                if (!Stringify::areEqual($rootPath, '/')){
                    $basePath = Stringify::trim($rootPath);
                }
                
                if (!empty($controller)){
                    $definedActions = $routeParameters['action'] ?? null;
                   
                    if (!empty($definedActions)){
                        foreach ($definedActions as $actionParameters){
                            $route = $this->createRoute($basePath, $controller, $actionParameters, $routeName);
                            $this->addRoute($route);
                        }
                    }
                    else{
                        throw Exception\RouteManagerException::requiredRouteParameterException('action');
                    }
                }
                else{
                    throw Exception\RouteManagerException::requiredRouteParameterException('controller');
                }
                
                
            }
            else{
                throw Exception\RouteManagerException::requiredRouteParameterException('path');
            }
            
            /*foreach ($this->routes as $route){
                var_dump($route);
                echo '<br /><br />';
            }*/
        }
    }
    
    /**
     * 
     * @param string $basePath
     * @param string $controller
     * @param array $actionParameters
     * @param string $routeName Route's name
     * @return \Sway\Component\Route\Route
     * @throws RouteManagerException
     */
    protected function createRoute(string $basePath, string $controller, array $actionParameters, string $routeName) : Route
    {
        if (!isset($actionParameters['path'])){
            throw Exception\RouteManagerException::requiredRouteParameterException('path');
        }
        if (!isset($actionParameters['listener'])){
            throw Exception\RouteManagerException::requiredRouteParameterException('listener');
        }
        
        /* Creates an empty route instance */
        $route = new Route();
        /**
         * Injects dependency container into $route
         */
        $this->getDependency('injector')->inject($route);
        /* Creates completed route's path */
        $completedRoutePath = Stringify::join($basePath, $actionParameters['path']);
        /* Creates a route's controller path */
        $routeControllerPath = Stringify::joinBy($controller, $actionParameters['listener'], ':');
        /* Creates full route's name */
        $fullRouteName = Stringify::joinBy($routeName, $actionParameters['listener'], ".");
        
        $route->setCompletedPath($completedRoutePath);
        $route->setController($routeControllerPath);
        
        if (isset($actionParameters['method'])){
            $route->setMethods($actionParameters['method']);
        }
        
        /** Array which contains only route's parameters */
        $routeParameters = $this->getParametersFromRoutePath($actionParameters['path']);
        
        /** Array which contains route's parameters with defaults values (parameter => defaultValue) */
        $routeParametersWithDefault = $this->combineParametersWithDefaults($routeParameters, 
                $actionParameters['default'] ?? array());
        
        $route->setParameters($routeParametersWithDefault);
        $route->setFullRouteName($fullRouteName);
        
        if (isset($actionParameters['directive'])){
            $route->setDirectives($actionParameters['directive']);
        }
        
        
        return $route;
        
    }
    
    /**
     * Gets parameters from route's path
     * @param string $routePath
     * @return array
     */
    protected function getParametersFromRoutePath(string $routePath) : array
    {
        /* Regular expression to match parameter(s) from route's path */
        $parameterPattern = "\{([a-zA-Z0-9\\-]+)\}";
        
        $regex = new Regex($routePath);
        
        /**
         * Matched parameters from route's path
         */
        $parametersFromRoutePath = $regex->findAll($parameterPattern);
        
        if (!empty($parametersFromRoutePath)){
            return $parametersFromRoutePath[1];
        }
        else{
            return array();
        }
       
    }
    
    /**
     * Creates an array which contains route's parameters with defaults values (parameterName => defaultValue|null)
     * @param array $routeParameters
     * @param array $parametersDefaults
     * @return array
     */
    protected function combineParametersWithDefaults(array $routeParameters, array $parametersDefaults) : array
    {
        $parametersWithDefaults = array();
        
        foreach ($routeParameters as $routeParameter){
            if (array_key_exists($routeParameter, $parametersDefaults)){
                $parametersWithDefaults[$routeParameter] = $parametersDefaults[$routeParameter];
            }
            else{
                $parametersWithDefaults[$routeParameter] = null;
            }
        }
        
        return $parametersWithDefaults;
    }
    
    
    public function adjustRouteController()
    {
        $routePath = $this->httpdInterface->getRoutePath();
        
        
        foreach ($this->routes as $route){
            $isRouteSuits = $route->isRoutePathSuits($routePath);
            
            if ($isRouteSuits){
                
                $response = $route->executeRouteController();
                
                if (!$response instanceof Response){
                    throw Exception\RouteManagerException::emptyControllerResponse();
                }
                
                /**
                 * Pass controller's response to httpd
                 */
                $this->getDependency('httpd')->serviceResponse($response);
                
                break;
            }
            
        }
    }
    
    /**
     * Gets route object by route's name
     * @param string $fullRouteName
     * @return \Sway\Component\Route\Route
     */
    protected function getRouteByRouteName(string $fullRouteName) : Route
    {
        foreach ($this->routes as $route){
            if  ($route->getFullRouteName() === $fullRouteName){
                return $route;
            }
        }
        
        /**
         * Throws an exception when not found
         */
        throw Exception\RouteManagerException::routeNotFoundByRouteName($fullRouteName);
    }
    
    public function createUri(string $fullRouteName, array $routeParameters)
    {
        /**
         * Gets route's object
         */
        $route = $this->getRouteByRouteName($fullRouteName);
        
        return $route->compilePath($routeParameters);      
    }
    
}

?>

