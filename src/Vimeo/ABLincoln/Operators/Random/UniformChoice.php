<?php

namespace Vimeo\ABLincoln\Operators\Random;

use \Vimeo\ABLincoln\Operators\RandomOperator;

/**
 * Randomly select a choice from an array of options
 *
 * Required Inputs:
 *   - 'choices': array of elements to draw from
 * Optional Inputs: None
 */
class UniformChoice extends RandomOperator
{
    /**
     * Choose an element randomly from the parameter array
     *
     * @return mixed the element chosen from the given array
     */
    protected function _simpleExecute()
    {
        $choices = $this->parameters['choices'];
        $num_choices = count($choices);
        if (!$num_choices) {
            return array();
        }
        return $choices[$this->_getHash() % $num_choices];
    }
}
