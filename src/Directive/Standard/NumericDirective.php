<?php

namespace Sway\Component\Route\Directive\Standard;

class NumericDirective
{
    /**
     * Checks if given value is numeric
     * @param type $parameterValue
     * @return bool
     */
    public static function isNumeric($parameterValue) : bool
    {
        return is_numeric($parameterValue);
    }
    
    /**
     * Checks if given value is integer
     * @param type $parameterValue
     * @return bool
     */
    public static function isInteger($parameterValue) : bool
    {
        $regularExpression = '/^[0-9]+$/';
        return (bool) preg_match($regularExpression, (string) $parameterValue);
    }
    
}


?>