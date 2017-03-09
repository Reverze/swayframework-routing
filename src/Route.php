<?php

namespace Sway\Component\Route;

use Sway\Component\Text\Stringify;
use Sway\Component\Dependency\DependencyInterface;
use Sway\Component\Regex\Regex;


class Route extends DependencyInterface
{
    /**
     * Route's completed path
     * @var string
     */
    private $path = null;
    
    /**
     * Route's controller
     * @var string
     */
    private $controller = null;
    
    /**
     * Accepted method by route
     * @var array
     */
    private $methods = null;
    
    /**
     * Route's parameters with defaults values
     * @var array
     */
    private $parameters = array();
    
    /**
     * Directives setted for parameters
     * @var array
     */
    private $directives = array();
    /**
     * Httpd's interface
     * @var \Sway\Component\Httpd\HttpdInterface
     */
    private $httpInterface = null;
    
    /**
     * Route directive container
     * @var \Sway\Component\Route\Directive\DirectiveContainer
     */
    private $directiveContainer = null;
    
    /**
     * Route's parameters parsed after isRoutePathSuits
     * @var array
     */
    private $parametersParsed = null;
    
    /**
     * Controller's invoker
     * @var \Sway\Component\Controller\Invoker
     */
    private $controllerInvoker = null;
    
    /**
     * Full route's name
     * @var string
     */
    private $fullRouteName = null;
    
    /**
     * Creates empty Route instance
     */
    public function __construct()
    {
        /**
         * For default routes accepts every http methods
         */
        $this->methods = [
            'GET', 'POST', 'PUT', 'DELETE'
        ];
        
        /** For default, route doesnt have defined parameters */
        $this->parameters = array();
    }
    
    
    protected function dependencyController() 
    {
        $this->httpInterface = $this->getDependency('httpd');
        $this->directiveContainer = $this->getDependency('routeDirective');
        $this->controllerInvoker = $this->getDependency('controllerInvoker');
    }
    /**
     * Sets completed path of route
     * @param string $completedPath
     */
    public function setCompletedPath(string $completedPath)
    {
        $this->path = $completedPath;
    }
    
    /**
     * Sets controller invoke path (example: master\className:method)
     * @param string $controller
     */
    public function setController(string $controller)
    {
        $this->controller = $controller;
    }
    
    /**
     * Sets accepted methods by route
     * @param array $methods
     */
    public function setMethods(array $methods)
    {
        $this->methods = array();
        
        foreach ($methods as $method){
            if ($this->httpInterface->isMethodDefined($method)){
                array_push($this->methods, strtoupper($method));
            }
        }
        
    }
    
    /**
     * Sets parameters with defaults value. (parameter => value|null)
     * @param array $parameters
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }
    
    /**
     * Sets directives which are used in route
     * @param array $directives
     */
    public function setDirectives(array $directives)
    {
        $this->directives = $directives;
    }
    
    /**
     * Sets full route's name
     * @param string $fullRouteName
     */
    public function setFullRouteName(string $fullRouteName)
    {
        $this->fullRouteName = $fullRouteName;
    }
    
    /**
     * Gets full route's name
     * @return string
     */
    public function getFullRouteName() : string
    {
        return $this->fullRouteName;
    }
    
    
    
    public function isRoutePathSuits(string $routePath)
    {
        /**
         * Explode route path into parts
         */
        $explodedRoutePath = explode('/', $routePath);
        
        /**
         * Explode route path which belongs to questioned route object
         */
        $explodedOwnRoutePath = explode ('/', $this->path);
        
        /**
         * If correctness if equal size of explodedOwnRoutePath array,
         * route suits
         */
        $routeCorrectness = 0;
        
        /**
         * Inserts default values if needed
         */
        $explodedRoutePath = $this->completeWithDefaultValues($explodedRoutePath, $explodedOwnRoutePath);
        
        
        /**
         * If sizes of arrays are not equal
         */
        if (sizeof($explodedRoutePath) !== sizeof($explodedOwnRoutePath)){
            $routeCorrectness = 0;
        }
        else if (sizeof($explodedRoutePath) === sizeof($explodedOwnRoutePath)){
            for ($partPointer = 0; $partPointer < sizeof($explodedRoutePath); $partPointer++){

                if ($this->isRouteParameter($explodedOwnRoutePath[$partPointer])){
                    $parameterName = $this->getParameterName($explodedOwnRoutePath[$partPointer]);
                    
                    $directivesResult = $this->executeDirectives($parameterName, $explodedRoutePath[$partPointer]);
                    
                    
                    if ($directivesResult){
                        $this->parametersParsed[$parameterName] = $explodedRoutePath[$partPointer];
                        $routeCorrectness++;
                        /**
                         * If parameter's value is null
                         */
                        if (empty($this->parametersParsed[$parameterName])){
                            $routeCorrectness--;
                        }
                    }
                }
                else{
                    if ($explodedRoutePath[$partPointer] === $explodedOwnRoutePath[$partPointer]){
                        $routeCorrectness++;
                    }
                }

            }
        }
        
        if ($routeCorrectness === sizeof($explodedOwnRoutePath)){
          
            return true;
        }
        
        return false;
    }
    
    /**
     * Checks if route part is parameter
     * @param string $expression
     * @return bool
     */
    protected function isRouteParameter(string $expression) : bool
    {
        if (!strlen($expression)){
            return false;
        }
        /**
         * Regular expression to match route's parameter
         */
        $parameterPattern = "\{([a-zA-Z0-9\\-]+)\}";  
        $regex = new Regex($expression); 
        return $regex->isMatch($parameterPattern);
    }
    
    /**
     * Gets parameter's name
     * @param string $expression
     * @return string
     */
    protected function getParameterName(string $expression) : string
    {
        /**
         * Regular expression to match route's parameter
         */
        $parameterPattern = "\{([a-zA-Z0-9\\-]+)\}";
        $regex = new Regex($expression);
        $matched = $regex->find($parameterPattern);
        
        return $matched[1];
    }
    
    protected function completeWithDefaultValues(array $explodedRoutePath, array $explodedOwnRoutePath)
    {
        $explodedRoutePathCompleted = $explodedRoutePath;
        
        /**
         * Wstawianie domyslnych wartosci parametrow do poszczegolnych pol w tablicy
         */
        for ($pointer = 0; $pointer < sizeof($explodedOwnRoutePath); $pointer++){
            if ($this->isRouteParameter($explodedOwnRoutePath[$pointer])){
                $parameterName = $this->getParameterName($explodedOwnRoutePath[$pointer]);
                $defaultValue = $this->getDefaultValueOfParameter($parameterName);
                $explodedRoutePathCompleted[$pointer] = $defaultValue;
            }
        }
        
        for ($pointer = 0; $pointer < sizeof($explodedRoutePath); $pointer++){
            $explodedRoutePathCompleted[$pointer] = $explodedRoutePath[$pointer];
            
        }
        
        return $explodedRoutePathCompleted;
    }
    
    /**
     * Gets default value of given parameter
     * @param string $parameterName
     * @return mixed
     */
    protected function getDefaultValueOfParameter(string $parameterName)
    {
        return $this->parameters[$parameterName];
    }
    
    
    /**
     * Temponary function for debugging
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
    
    /**
     * Executes all directives on route's parameter
     * @param string $parameterName
     * @param type $givenValue
     * @return boolean
     */
    public function executeDirectives(string $parameterName, $givenValue)
    {
        /**
         * If directives are not set for parameter, parameter is valid
         */
        if (!isset($this->directives[$parameterName])){
            return true;
        }
        
        $executorResult = $this->directiveContainer->executeDirectives($givenValue, 
                $this->directives[$parameterName]);
        
        return $executorResult;
    }
    
    public function executeRouteController()
    {
        return $this->controllerInvoker->executeController($this->controller, $this->parametersParsed); 
    }
    
    /**
     * Compiles route's path 
     * @param array $routeParameters
     * @return string
     * @throws \Sway\Component\Route\Exception\RouteException
     */
    public function compilePath(array $routeParameters)
    {
        /**
         * Parameters to insert (eg. after executed directives).
         * Filters are disabled in this case
         */
        $parametersToInsert = array();
        
        
        foreach ($this->parameters as $parameterName => $parameterValue){
            /**
             * If parameter is omitted (and parameter doesnt have default value)
             */
            
            
            if (!array_key_exists($parameterName, $routeParameters) && empty($parameterValue)){
                throw Exception\RouteException::missedParameterCompileUri($parameterName);
            }
            
            /**
             * Parameter's value cannot be empty (null)
             */
            if (empty($routeParameters[$parameterName]) && empty($parameterValue)){
                throw Exception\RouteException::emptyParameterValueCompileUri($parameterName);
            }
            
            /**
             * Selects defaults parameter's value or value specified by user
             */
            $value = null;
            
            if (empty($routeParameters[$parameterName])){
                $value = $parameterValue;
            }
            else{
                $value = $routeParameters[$parameterName];
            }
            
            /**
             * Executes directives on parameter
             */
            $directiveExecutorResult = $this->executeDirectives($parameterName, $value);
            
            /**
             * When parameter is not passed by directive executor
             */
            if (!$directiveExecutorResult){
                throw Exception\RouteException::parameterDirectiveFail($parameterName);
            }
            
            $parametersToInsert[$parameterName] = $value;
            
        }//foreach
        
        
        $explodedRoutePath = explode("/", $this->path);
        
        $compiledRoutePath = "/";
        
        foreach ($explodedRoutePath as $routePathPart){
            if (!strlen($routePathPart)){
                continue;
            }
            
            if ($this->isRouteParameter($routePathPart)){
                $parameterName = $this->getParameterName($routePathPart);
                $compiledRoutePath .= (string) $parametersToInsert[$parameterName];
            }
            else{
                $compiledRoutePath .= $routePathPart;
            }
            
            $compiledRoutePath .= '/';     
        }
        
        return $compiledRoutePath;
    }
    
    
    
}

?>
