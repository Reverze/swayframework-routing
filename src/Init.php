<?php

namespace Sway\Component\Route;

use Sway\Component\Dependency\DependencyInterface;
use Sway\Component\Init\Component;

class Init extends Component
{
    /**
     * Array which contains routes definitions
     * @var array
     */
    private $routes = array();
    
    /**
     * 
     */
    protected function dependencyController() 
    {
        if ($this->getDependency('framework')->hasCfg('framework/routing/routes')){
            $this->routes = $this->getDependency('framework')->getCfg('framework/routing/routes');
        }
    }
    
    /**
     * Initializes router
     * @return \Sway\Component\Route\Router
     */
    public function init()
    {
        $router = Router::create();
        $this->getDependency('injector')->inject($router);
        $router->setRoutes($this->routes);
        $this->setAsInjected();
        return $router;     
    }
    
}

?>
