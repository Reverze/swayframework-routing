<?php

namespace Sway\Component\Route\Directive;

use Sway\Component\Dependency\DependencyInterface;

class DirectiveExecutor extends DependencyInterface
{
    /**
     * Directives container
     * @var \Sway\Component\Route\Directive\DirectiveContainer
     */
    private $directiveContainer = null;
    
    
    public function __construct()
    {
        
    }
 
    protected function dependencyController() 
    {
        $this->directiveContainer = $this->getDependency('routeDirective');
    }
    
    /**
     * Executes all given directives on given value.
     * If all directives returns true, it means that value ..
     * @param type $givenValue
     * @param array $directives
     * @return boolean
     */
    public function executeDirectives($givenValue, array $directives)
    {
        foreach ($directives as $directive){
            $directiveObject = $this->directiveContainer->getDirective($directive);
            
            if (!$directiveObject->executeOn($givenValue)){
                return false;
            }
        }
        
        return true;
    }
}


?>

