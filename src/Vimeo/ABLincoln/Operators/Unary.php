<?php

namespace Vimeo\ABLincoln\Operators;

/**
 * Interface for defining unary operators.
 */
abstract class Unary extends AbstractOperator
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
     * @return mixed the evaluated expression
     */
    protected function simpleExecute()
    {
        return $this->unaryExecute($this->parameters['value']);
    }

    /**
     * Implement with unary operator functionality
     *
     * @param mixed $value the single operand
     * @return mixed the evaluated expression
     */
    abstract protected function unaryExecute($value);
}