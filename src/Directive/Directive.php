<?php

namespace Sway\Component\Route\Directive;

use Sway\Component\Route\Directive\Exception;
use Sway\Component\Text\Stringify;
use Sway\Component\Dependency\DependencyInterface;

class Directive extends DependencyInterface
{
    /**
     * Directive's name
     * @var string
     */
    private $directiveName = null;
    
    /**
     * Directive's controller (controller:method)
     * @var string
     */
    private $controller = null;
    
    /**
     * Directives action controller
     * @var \Sway\Component\Route\Directive\DirectiveAction
     */
    private $action = null;
    
    /**
     * Controllers invoker
     * @var \Sway\Component\Controller\Invoker
     */
    private $controllerInvoker = null;
    
    public function __construct()
    {
        
    }
    
    protected function dependencyController() 
    {
        $this->controllerInvoker = $this->getDependency('controllerInvoker');
    }
    
    /**
     * Gets directive's name
     * @return string
     */
    public function getDirectiveName() : string
    {
        return $this->directiveName;
    }
    
    public function createFrom(string $directiveName, array $directiveParameters)
    {
        /* Directive's name cannot be empty */
        if (Stringify::isEmpty($directiveName)){
            throw Exception\DirectiveException::emptyDirectiveName();
        }
        
        $this->directiveName = $directiveName;
        /**
         * Controller must be defined
         */
        if (array_key_exists('controller', $directiveParameters)){
            /**
             * Required full controller path
             */
            if ($this->controllerInvoker->isValidControllerPath($directiveParameters['controller'])){
                $this->controller = $directiveParameters['controller'];
            }
            else{
                throw Exception\DirectiveException::invalidDirectiveControllerPath($directiveName);
            }
            
            
        }
        
        /**
         * For default direction action is empty
         */
        $this->action = DirectiveAction::createDefaultDirectiveAction();
        $this->getDependency('injector')->inject($this->action);
        
        if (array_key_exists('action', $directiveParameters)){
            
            $this->action->initializeFrom($directiveParameters['action']);
        }
        
    }
    
    /**
     * Executes directive's controller and returns result
     * @param type $value
     * @return bool
     */
    public function executeOn($value)
    {
        $callableController = $this->controllerInvoker->getCallableControllerPath($this->controller);
        
        $controllerResult = call_user_func_array($callableController, [
            $value
        ]);
        
        return $controllerResult;
    }
    
    
}


?>
