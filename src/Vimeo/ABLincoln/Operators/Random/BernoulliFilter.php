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
        if (!array_key_exists('p', $this->parameters) || !array_key_exists('choices', $this->parameters)) {
            throw new \Exception(get_class($this) . ": inputs 'p' and 'choices' required.");
        }

        $p = $this->parameters['p'];
        $choices = $this->parameters['choices'];
        if (!is_numeric($p) || $p < 0.0 || $p > 1.0) {
            throw new \Exception(get_class($this) . ": 'p' must be a number between 0.0 and 1.0, not $p.");
        }
        if (!is_array($choices)) {
            throw new \Exception(get_class($this) . ": 'choices' must be an array.");
        }

        if (empty($choices)) {
            return [];
        }
        return array_filter($choices, function($item) use ($p) {
            return $this->_getUniform(0.0, 1.0, $item) <= $p;
        });
    }
}
