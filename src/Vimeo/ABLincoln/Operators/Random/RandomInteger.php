<?php

namespace Vimeo\ABLincoln\Operators\Random;

use \Vimeo\ABLincoln\Operators\RandomOperator;

/**
 * Random operator used to calculate pseudorandom integers
 *
 * Required Inputs:
 *   - 'min': min (int) value drawn
 *   - 'max': max (int) value drawn
 * Optional Inputs: None
 */
class RandomInteger extends RandomOperator
{
    /**
     * Calculate a random integer in the given range
     *
     * @return int the calculated random integer
     */
    protected function _simpleExecute()
    {
        $min_val = $this->parameters['min'];
        $max_val = $this->parameters['max'];
        return $min_val + $this->_getHash() % ($max_val - $min_val + 1);
    }
}
