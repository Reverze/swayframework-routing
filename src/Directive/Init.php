<?php

namespace Sway\Component\Route\Directive;

use Sway\Component\Dependency\DependencyInterface;
use Sway\Component\Init\Component;

class Init extends Component
{
    /**
     * Array which contains directives
     * @var array
     */
    private $directives = array();
    
    protected function dependencyController()
    {
        if ($this->getDependency('framework')->hasCfg('framework/routing/directive')){
            $this->directives = $this->getDependency('framework')->getCfg('framework/routing/directive');
        }
    }
    
    /**
     * Initializes directive's container
     * @return \Sway\Component\Route\Directive\DirectiveContainer
     */
    public function init()
    {
        $directiveContainer = DirectiveContainer::create();
        $this->getDependency('injector')->inject($directiveContainer);
        $this->setAsInjected();
        $directiveContainer->appendDirectives($this->directives);
        return $directiveContainer;
    }
}


?>