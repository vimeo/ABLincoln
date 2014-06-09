<?php

namespace Vimeo\ABLincoln\Ops;

/**
 * The Assignment class is essentially an array (and can be used like one),
 * but allows the execution of random operators using the names of variables
 * as salts.
 */
class Assignment implements ArrayAccess
{
    private $data;

    /**
     * Store the given experiment salt in a private data array
     *
     * @param string $experiment_salt the experiment salt to store
     */
    public function __construct($experiment_salt)
    {
        $this->data = array("experiment_salt" => $experiment_salt);
    }

    /**
     * Evaluate a given parameter and return its value. Currently just directly
     * returns the given argument but can be modified with more complex behavior.
     *
     * @param mixed $value the parameter to evaluate
     */
    public function evaluate($value)
    {
        return $value;
    }

    /**
     * Check if a given key is set in the array
     *
     * @param mixed $offset key to check for in the array
     * @return boolean true if key exists, false otherwise
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * Get the value of a key in the array if it exists
     *
     * @param mixed $offset key to obtain the value of
     * @return mixed value of given key if it exists, null otherwise
     */
    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    /**
     * Set the value of a key in the array using the parameter name as salt
     *
     * @param mixed $offset key to set the value of
     * @param mixed $value value to set at the given array index
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
             if ($value instanceof AbOpRandom) {
                if (!array_key_exists('salt', $value->args)) {
                    $value->args['salt'] = $offset;
                }
                $this->data[] = $value->execute($this);
            }
            else {
                $this->data[] = $value;
            }
        }
        else {
            if ($value instanceof AbOpRandom) {
                if (!array_key_exists('salt', $value->args)) {
                    $value->args['salt'] = $offset;
                }
                $this->data[$offset] = $value->execute($this);
            }
            else {
                $this->data[$offset] = $value;
            }
        }
    }

    /**
     * Unset the value at a given key
     *
     * @param mixed $offset key unset the value of
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }
}