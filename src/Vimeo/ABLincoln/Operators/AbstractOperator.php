<?php

namespace Vimeo\ABLincoln\Operators;

/**
 * Easiest way to implement simple operators. The class automatically evaluates
 * the values of all parameters passed in via execute(), and stores the mapper 
 * object and evaluated parameters as instance variables.  The user can then 
 * extend AbstractOperator and implement simpleExecute().
 */
abstract class AbstractOperator
{
    protected $parameters;
    protected $mapper;

    private $args;

    /**
     * Store the set of parameters to use as required and optional arguments
     *
     * @param array $options array mapping operator options to values
     * @param mixed $inputs input value/array used for hashing
     */
    public function __construct($options, $inputs)
    {
        $this->args = $options;
        $this->args['unit'] = $inputs;
    }

    /**
     * Evaluate all parameters and store as instance variables, then execute
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

    /**
      * Argument accessor
      *
      * @return array operator arguments
      */
     public function args()
     {
         return $this->args;
     }
 
     /**
      * Argument setter
      *
      * @param mixed $key name of argument to set
      * @param mixed $value value to set argument
      */
     public function setArg($key, $value)
     {
         $this->args[$key] = $value;
     }

    /**
     * All operators must specify required and optional arguments to be used in
     * execute() by defining options(). The function should return an array 
     * formatted like so:
     *   array (
     *     'p' => array('required' => 1, 'description' => 'probability of success')
     *     'n' => array('required' => 0, 'description' => 'number of samples')
     *   )
     *
     * @return array array of required and optional arguments
     */
    public function options()
    {
        return array();
    }

    /**
     * Get array of all parameters belonging to the operation instance.
     *
     * @return array parameter array
     */
    public function getOptions()
    {
        if (get_class($this) === get_class()) {
            return array();
        }
        $instance_ops = options();
        $parent_ops = parent::getOptions();
        return array_merge($parent_ops, $instance_ops);
    }

    /**
     * Get the description of a given parameter belonging to the operator
     *
     * @param string $op_name name of the parameter to get the description of
     * @return string description of the given parameter
     */
    public function getOptionDescription($op_name)
    {
        $ops = $this->getOptions();
        return isset($ops[$op_name]) ? $ops[$op_name]['description'] : $op_name;
    }

    /**
     * Get whether a given parameter is required
     *
     * @param string $op_name name of the parameter to check
     * @return 1 if required, 0 otherwise
     */
    public function getOptionRequired($op_name)
    {
        $ops = $this->getOptions();
        return isset($ops[$op_name]) ? $ops[$op_name]['required'] : 1;
    }
}