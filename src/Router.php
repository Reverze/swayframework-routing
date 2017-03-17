<?php

namespace Sway\Component\Route;

use Sway\Component\Httpd\HttpdInterface;
use Sway\Component\Controller\Invoker;
use Sway\Component\Dependency\DependencyInterface;

use Sway\Component\Event\EventArgs;

class Router extends DependencyInterface
{
    /**
     * Route's manager
     * @var \Sway\Component\Route\Manager
     */
    private $routesManager = null;
    
    /**
     * Httpd interface
     * @var \Sway\Component\Httpd\HttpdInterface
     */
    private $httpdInterface = null;
    
    /**
     * Controller invoker
     * @var \Sway\Component\Controller\Invoker
     */
    private $controllerInvoker = null;
    
    public function __construct()
    {
        if (empty($this->routesManager)){
            $this->routesManager = new Manager();
           
        }
    }
    
    protected function dependencyController() 
    {
        $this->getDependency('injector')->inject($this->routesManager);
        $this->httpdInterface = $this->getDependency('httpd');
        $this->controllerInvoker = $this->getDependency('controllerInvoker');
    }
    
    
    /**
     * Registers routes from array
     * @param array $routes
     */
    public function setRoutes(array $routes)
    {
        $this->routesManager->registerRoutesFromArray($routes);
    }
    
    public function executeRoute()
    {
        $controller = $this->routesManager->adjustRouteController();
    }
    
    /**
     * Creates a new router
     * @param array $routes 
     * @return \Sway\Component\Route\Router
     */
    public static function create(array $routes = array()) : Router
    {
        $router = new Router();
        $router->setRoutes($routes);
        return $router;
    }
    
    public function createUri(string $fullRouteName, array $routeParameters)
    {
        return $this->routesManager->createUri($fullRouteName, $routeParameters);
    }
    
    public function launch(EventArgs $args, $object)
    {
        $this->executeRoute();
    }
}


?>
