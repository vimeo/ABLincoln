<?php

namespace Vimeo\ABLincoln\Ops;

/**
 * Abstract base class for operators.
 */
abstract class AbOp
{
    protected $args;

    /**
     * Store the set of parameters to use as required and optional arguments
     *
     * @param array $parameters array mapping operator parameters to values
     */
    public function __construct($parameters)
    {
        $this->args = $parameters;
    }

    /**
     * Execute the operator using its predefined arguments
     *
     * @param Assignment $mapper mapper object used to evaluate parameters
     */
    abstract public function execute($mapper);

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
        if (!strcmp(get_class($this), 'Op')) {
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

/**
 * Easiest way to implement simple operators. The class automatically evaluates
 * the values of all parameters passed in via execute(), and stores the mapper 
 * object and evaluated parameters as instance variables.  The user can then 
 * extend AbOpSimple and implement simpleExecute().
 */
abstract class AbOpSimple extends AbOp
{
    protected $mapper;
    protected $parameters;

    /**
     * Evaluate all parameters and store as instance variables, then executes
     * the operator as defined in simpleExecute()
     *
     * @param Assignment $mapper mapper object used to evaluate parameters
     * @return the evaluated expression
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
     * @return the evaluated expression
     */
    abstract protected function simpleExecute();
}

/**
 * Interface for defining binary operators.
 */
abstract class AbOpBinary extends AbOpSimple
{
    /**
     * Binary operators take a 'left' and 'right' operands
     *
     * @return array the array of required parameters
     */
    public function options()
    {
        return array(
            'left' => array(
                'required' => 1,
                'description' => 'left side of binary operator'
            ),
            'right' => array(
                'required' => 1,
                'description' => 'right side of binary operator'
            )
        );
    }

    /**
     * Evaluates the binary operator using both operands
     *
     * @return the evaluated expression
     */
    protected function simpleExecute()
    {
        return $this->binaryExecute(
            $this->parameters['left'],
            $this->parameters['right']
        );
    }

    /**
     * Implement with binary operator functionality
     *
     * @param $left the left operand
     * @param $right the right operand
     * @return the evaluated expression
     */
    abstract protected function binaryExecute($left, $right);
}

/**
 * Interface for defining unary operators.
 */
abstract class AbOpUnary extends AbOpSimple
{
    /**
     * Unary operators take a single 'value' operand
     *
     * @return array the array of required parameters
     */
    public function options()
    {
        return array(
            'value' => array(
                'required' => 1,
                'description' => 'input value to unary operator'
            )
        );
    }

    /**
     * Evaluates the unary operator using its single operand
     *
     * @return the evaluated expression
     */
    protected function simpleExecute()
    {
        return $this->unaryExecute($this->parameters['value']);
    }

    /**
     * Implement with unary operator functionality
     *
     * @param $value the single operand
     * @return the evaluated expression
     */
    abstract protected function unaryExecute($value);
}

/**
 * Interface for defining commutative operators.
 */
abstract class AbOpCommutative extends AbOpSimple
{
    /**
     * Commutative operators take a single 'values' array of operands
     *
     * @return array the array of required parameters
     */
    public function options()
    {
        return array(
            'values' => array(
                'required' => 1,
                'description' => 'input values to commutative operator'
            )
        );
    }

    /**
     * Evaluates the commutative operator using its array of operands
     *
     * @return the evaluated expression
     */
    protected function simpleExecute()
    {
        return $this->commutativeExecute($this->parameters['values']);
    }

    /**
     * Implement with commutative operator functionality
     *
     * @param array $values the array of operands
     * @return the evaluated expression
     */
    abstract protected function commutativeExecute($values);
}