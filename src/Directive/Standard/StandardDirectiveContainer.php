<?php

namespace Sway\Component\Route\Directive\Standard;

class StandardDirectiveContainer
{
    
    public function __construct()
    {
        
        
    }
    
    public function getDirectives()
    {
        return [
            'numeric' => [
                'controller' => 'Sway\Component\Route\Directive\Standard\NumericDirective:isNumeric',
                'action' => 'none'
            ],
            'integer' => [
                'controller' => 'Sway\Component\Route\Directive\Standard\NumericDirective:isInteger',
                'action' => 'none'
            ]
        ];
    }
    
}


?>
