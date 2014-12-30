<?php

namespace Vimeo\ABLincoln\Operators\Random;

use \Vimeo\ABLincoln\Operators\RandomOperator;

/**
 * Filter an array with Bernoulli Trial probability for each element
 *
 * Required Inputs:
 *   - 'p': probability of retaining element
 *   - 'choices': array of elements being filtered
 * Optional Inputs: None
 */
class BernoulliFilter extends RandomOperator
{
    /**
     * Filter the parameter array on each element with probability p
     *
     * @return array the filtered choices array
     */
    protected function _simpleExecute()
    {
        $p = $this->parameters['p'];
        $choices = $this->parameters['choices'];
        if (empty($choices)) {
            return array();
        }
        return array_filter($choices, function($item) use ($p) {
            return $this->_getUniform(0.0, 1.0, $item) <= $p;
        });
    }
}
