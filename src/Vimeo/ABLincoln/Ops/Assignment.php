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

    public function offsetExists()
    {

    }

    public function offsetGet()
    {

    }

    public function offsetSet()
    {

    }

    public function offsetUnset()
    {
        
    }
}