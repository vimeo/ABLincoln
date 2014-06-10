<?php

namespace Vimeo\ABLincoln\Ops\Base;

/**
 * Interface for defining commutative operators.
 */
abstract class OpCommutative extends OpSimple
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
     * @return mixed the evaluated expression
     */
    protected function simpleExecute()
    {
        return $this->commutativeExecute($this->parameters['values']);
    }

    /**
     * Implement with commutative operator functionality
     *
     * @param array $values the array of operands
     * @return mixed the evaluated expression
     */
    abstract protected function commutativeExecute($values);
}