<?php

namespace Sway\Component\Route\Directive\Exception;

class DirectiveException extends \Exception
{
    /**
     * Throws an exception when directive object is empty (is null)
     * @return \Sway\Component\Route\Directive\Exception\DirectiveException
     */
    public static function emptyDirective() : DirectiveException
    {
        return (new DirectiveException(sprintf("Directive is empty (is null)")));
    }
    
    /**
     * Throws an exception when directive's name is empty 
     * @return \Sway\Component\Route\Directive\Exception\DirectiveException
     */
    public static function emptyDirectiveName() : DirectiveException
    {
        return (new DirectiveException(sprintf("Directive's name is empty")));
    }
    
    /**
     * Throws an exception when directive's controller is not defined
     * @param string $directiveName
     * @return \Sway\Component\Route\Directive\Exception\DirectiveException
     */
    public static function missedDirectiveController(string $directiveName) : DirectiveException
    {
        return (new DirectiveException(sprintf("Controller is not defined for '%s' directive", $directiveName)));
    }
    
    /**
     * Throws an exception when directive's controller has invalid path
     * @param string $directiveName
     * @return \Sway\Component\Route\Directive\Exception\DirectiveException
     */
    public static function invalidDirectiveControllerPath(string $directiveName) : DirectiveException
    {
        return (new DirectiveException(sprintf("Controller path is invalid for '%s' directive", $directiveName)));
    }
    
    /**
     * Throws an exception when directive was not found
     * @param string $directiveName
     * @return \Sway\Component\Route\Directive\Exception\DirectiveException
     */
    public static function directiveNotFound(string $directiveName) : DirectiveException
    {
        return (new DirectiveException(sprintf("Route parameter directive '%s' not found", $directiveName)));
    }
}

?>

