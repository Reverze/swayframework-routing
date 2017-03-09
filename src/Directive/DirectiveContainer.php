<?php

namespace Sway\Component\Route\Directive;

use Sway\Component\Route\Directive\Exception;
use Sway\Component\Dependency\DependencyInterface;
use Sway\Component\Text\Stringify;
use Sway\Component\Regex\Regex;


class DirectiveContainer extends DependencyInterface
{
    /**
     * Array which stores directive's objects
     * @var array
     */
    private $directives = array();
    
    public function __construct()
    {
        
    }
    
    protected function dependencyController()
    {
        $standardDirectives = new Standard\StandardDirectiveContainer();
        $this->appendDirectives($standardDirectives->getDirectives());
    }
    
    /**
     * Creates empty route's directive container
     * @param array $directives
     * @return \Sway\Component\Route\Directive\DirectiveContainer
     */
    public static function create(array $directives = array()) : DirectiveContainer
    {
        $directiveContainer = new DirectiveContainer();
        $directiveContainer->appendDirectives($directives);
        return $directiveContainer;
    }
    
    /**
     * Appends directive into container
     * @param \Sway\Component\Route\Directive\Directive $directive
     * @throws \Sway\Component\Route\Directive\Exception\DirectiveException
     */
    public function appendDirective(Directive $directive)
    {
        if (empty($directive)){
            throw Exception\DirectiveException::emptyDirective();
        }
        
        array_push($this->directives, $directive);
    }
    
    public function appendDirectives(array $directivesArray)
    {
        foreach ($directivesArray as $directiveName => $directiveParameters){
            $directive = $this->createDirective($directiveName, $directiveParameters);
            $this->appendDirective($directive);
        }
        
       
    }
    
    /**
     * Creates directive object based on given parameters
     * @param string $directiveName
     * @param array $directiveParameters
     * @return \Sway\Component\Route\Directive\Directive
     */
    protected function createDirective(string $directiveName, array $directiveParameters) : Directive
    {
        $directive = new Directive();
        $this->getDependency('injector')->inject($directive);
        $directive->createFrom($directiveName, $directiveParameters);
        return $directive;
    }
    
    /**
     * Gets directive
     * @param string $directiveName
     * @return \Sway\Component\Route\Directive\Directive
     * @throws \Sway\Component\Route\Directive\Exception\DirectiveException
     */
    public function getDirective(string $directiveName) : Directive
    {
        foreach ($this->directives as $directive){
            if ($directive->getDirectiveName() === $directiveName){
                return $directive;
            }
        }
        
        throw Exception\DirectiveException::directiveNotFound($directiveName);
    }
    
    /**
     * Executes route's parameters directives
     * @param type $givenValue
     * @param array $requiredDirectives
     * @return bool
     */
    public function executeDirectives($givenValue, array $requiredDirectives)
    {
        $directiveExecutor = new DirectiveExecutor();
        $this->getDependency('injector')->inject($directiveExecutor);
        
        return $directiveExecutor->executeDirectives($givenValue, $requiredDirectives);
    }
}


?>

