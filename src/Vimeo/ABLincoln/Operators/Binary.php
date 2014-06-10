<?php

namespace Vimeo\ABLincoln\Operators;

/**
 * Interface for defining binary operators.
 */
abstract class Binary extends AbstractOperator
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
     * @return mixed the evaluated expression
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
     * @param mixed $left the left operand
     * @param mixed $right the right operand
     * @return mixed the evaluated expression
     */
    abstract protected function binaryExecute($left, $right);
}