<?php

namespace Vimeo\ABLincoln;

use \Vimeo\ABLincoln\Operators\RandomOperator;

/**
 * The Assignment class is essentially an array but allows the execution of
 * random operators using the names of variables as salts.
 */
class Assignment
{
    private $experiment_salt;
    private $overrides;
    private $data;

    /**
     * Store the given experiment salt for future use
     *
     * @param string $experiment_salt the experiment salt to store
     */
    public function __construct($experiment_salt, $overrides = [])
    {
        $this->experiment_salt = $experiment_salt;
        $this->overrides = $overrides;
        $this->data = $overrides;
    }

    /**
     * Evaluate a given parameter and return its value. Currently just directly
     * returns the given argument but can be modified with more complex behavior
     *
     * @param mixed $value the parameter to evaluate
     */
    public function evaluate($value)
    {
        return $value;
    }

    /**
     * Get the array representation of this Assignment
     *
     * @return array the Assignment's array representation
     */
    public function asArray()
    {
        return $this->data;
    }

    /**
     * Get an array of all Assignment parameter overrides
     *
     * @return array the override array
     */
    public function getOverrides()
    {
        return $this->overrides;
    }

    /**
     * Set overrides for the Assignment parameters
     *
     * @param array $overrides parameter name/value pairs to use as overrides
     */
    public function setOverrides($overrides)
    {
        $this->overrides = $overrides;
        $this->data = array_replace($this->data, $overrides);
    }

    /**
     * Check if a given key is set in the Assignment object
     *
     * @param string $name key to check for in the object
     * @return boolean true if key set, false otherwise
     */
    public function __isset($name)
    {
        if ($name === 'experiment_salt') {
            return isset($this->experiment_salt);
        }

        return isset($this->data[$name]);
    }

    /**
     * Get the value of a key in the Assignment object if it exists
     *
     * @param string $name key to obtain the value of
     * @return mixed value of given key if it exists, null otherwise
     */
    public function __get($name)
    {
        if ($name === 'experiment_salt') {
            return $this->experiment_salt;
        }

        return array_key_exists($name, $this->data) ? $this->data[$name] : null;
    }

    /**
     * Set the value of a key in the object using the parameter name as salt
     *
     * @param string $name key to set the value of
     * @param mixed $value value to set at the given index
     */
    public function __set($name, $value)
    {
        if ($name === 'experiment_salt') {
            $this->experiment_salt = $value;
            return;
        }

        if (array_key_exists($name, $this->overrides)) {
            return;
        }

        if ($value instanceof RandomOperator) {
            if (!array_key_exists('salt', $value->args())) {
                $value->setArg('salt', $name);
            }
            $this->data[$name] = $value->execute($this);
        }
        else {
            $this->data[$name] = $value;
        }
    }

    /**
     * Unset the value at a given key
     *
     * @param string $name key to unset the value of
     */
    public function __unset($name)
    {
        if ($name === 'experiment_salt') {
            unset($this->experiment_salt);
            return;
        }

        unset($this->data[$name]);
    }
}
