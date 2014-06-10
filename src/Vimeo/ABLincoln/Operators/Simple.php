<?php

namespace Vimeo\ABLincoln\Ops\Base;

/**
 * Easiest way to implement simple operators. The class automatically evaluates
 * the values of all parameters passed in via execute(), and stores the mapper 
 * object and evaluated parameters as instance variables.  The user can then 
 * extend AbOpSimple and implement simpleExecute().
 */
abstract class OpSimple extends Op
{
    protected $mapper;
    protected $parameters;

    /**
     * Evaluate all parameters and store as instance variables, then executes
     * the operator as defined in simpleExecute()
     *
     * @param Assignment $mapper mapper object used to evaluate parameters
     * @return mixed the evaluated expression
     */
    public function execute($mapper)
    {
        $this->mapper = $mapper;
        $this->parameters = array();  // evaluated parameters
        foreach ($this->args as $key => $val) {
            $this->parameters[$key] = $mapper->evaluate($val);
        }
        return $this->simpleExecute();
    }

    /**
     * Implement with operator functionality
     *
     * @return mixed the evaluated expression
     */
    abstract protected function simpleExecute();
}