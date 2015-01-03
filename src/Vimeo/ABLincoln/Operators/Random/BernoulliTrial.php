<?php

namespace Vimeo\ABLincoln\Operators\Random;

use \Vimeo\ABLincoln\Operators\RandomOperator;

/**
 * Simulate a Bernoulli Trial by choosing 1 or 0 with a given probability
 *
 * Required Inputs:
 *   - 'p': probability of drawing 1
 * Optional Inputs: None
 */
class BernoulliTrial extends RandomOperator
{
    /**
     * Calculate either 1 or 0 with a given probability
     *
     * @return int 1 with probability p, 0 otherwise
     */
    protected function _simpleExecute()
    {
        if (!array_key_exists('p', $this->parameters)) {
            throw new \Exception(get_class($this) . ": input 'p' required.");
        }

        $p = $this->parameters['p'];
        $rand_val = $this->_getUniform(0.0, 1.0);

        if (!is_numeric($p) || $p < 0.0 || $p > 1.0) {
            throw new \Exception(get_class($this) . ": 'p' must be a number between 0.0 and 1.0, not $p.");
        }

        return ($rand_val <= $p) ? 1 : 0;
    }
}
