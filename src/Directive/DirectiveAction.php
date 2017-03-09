<?php

namespace Sway\Component\Route\Directive;

use Sway\Component\Dependency\DependencyInterface;
use Sway\Component\Text\Stringify;

class DirectiveAction extends DependencyInterface
{
    /**
     * Redirect to action
     * @var string
     */
    private $redirectTo = null;
    
    /**
     * None action
     * @var bool
     */
    private $none = null;
    
    /**
     * Trigger route not found action
     * @var bool
     */
    private $triggerRouteNotFound = false;
    
    public function __construct(bool $default = false)
    {
        $this->none = false;
        
        if ($default){
            $this->triggerRouteNotFound = true;
        }
    }
    
    /**
     * Creates a default directive action.
     * When directive returns false, default action is to trigger that route is not found
     * @return \Sway\Component\Route\Directive\DirectiveAction
     */
    public static function createDefaultDirectiveAction() : DirectiveAction
    {
        $directiveAction = new DirectiveAction(true);
        return $directiveAction;
    }
    
    
    /**
     * Initalizes directive action from given parameters
     * @param type $actionParameters
     */
    public function initializeFrom($actionParameters)
    {
        if ($actionParameters === 'none'){
            $this->none = true;
        }
        else if (isset($actionParameters['redirectTo'])){
            $this->redirectTo = $actionParameters['redirectTo'];
        }
        else{
            $this->triggerRouteNotFound = true;
        }  
        
    }
}


?>

