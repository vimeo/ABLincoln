<?php

namespace Vimeo\ABLincoln\Ops;

class Assignment implements ArrayAccess
{
    private $experiment_salt;
    private $data;

    public function __construct($experiment_salt)
    {
        $this->experiment_salt = $experiment_salt;
        $this->data = array();
    }

    public function evaluate($value)
    {
        return $value;
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
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

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }
}