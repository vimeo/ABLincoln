<?php
/**
 * Most basic operaator class from which all others inherit
 */
abstract class Op
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
     */
    abstract public function execute();

    /**
     * All operators must specify required and optional arguments to be used in
     * execute() by defining options(). The function should return an array 
     * formatted like so:
     *   array (
     *     'p' => array('required' => 1, 'description' => 'probability of success')
     *     'n' => array('required' => 0, 'description' => 'number of samples')
     *   )
     *
     * @return array of required and optional arguments
     */
    public function options()
    {
        return array();
    }

    /**
     * Recursively append parents' options() with instance's options(). Only
     * gets called by Op base class
     *
     * @return array containing all ancestors' options
     */
    private function optionMerge()
    {
        if (!strcmp(get_class(), 'Op')) {
            return array();
        }
        $instance_ops = options();
        $parent_ops = parent::optionMerge();
        return array_merge($parent_ops, $instance_ops);
    }

    /**
     * Get array of all parameters belonging to the operation instance.
     *
     * @return parameter array
     */
    public function getOptions()
    {
        return $this->optionMerge();
    }

    /**
     * Get the description of a given parameter belonging to the operator
     *
     * @param string $op_name name of the parameter to get the description of
     * @return description of the given parameter
     */
    public function getOptionDescription($op_name)
    {
        $ops = $this->optionMerge();
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
        $ops = $this->optionMerge();\
        return isset($ops[$op_name]) ? $ops[$op_name]['required'] : 1;
    }
}