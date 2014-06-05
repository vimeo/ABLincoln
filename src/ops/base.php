<?php
/**
 * Abstract operator class
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

abstract class AbOpSimple extends AbOp
{
    protected $mapper;
    protected $parameters;

    public function execute($mapper)
    {
        $this->mapper = $mapper;
        $this->parameters = array();  // evaluated parameters
        foreach ($this->args as $key => $val) {
            $this->parameters[$key] = $mapper->evaluate($val);
        }
        return $this->simpleExecute();
    }

    abstract protected function simpleExecute();
}