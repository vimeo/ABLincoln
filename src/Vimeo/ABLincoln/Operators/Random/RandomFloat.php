<?php

namespace Vimeo\ABLincoln\Operators\Random;

use \Vimeo\ABLincoln\Operators\RandomOperator;

/**
 * Random operator used to calculate pseudorandom floating point numbers
 *
 * Required Inputs:
 *   - 'min': min (float) value drawn
 *   - 'max': max (float) value drawn
 * Optional Inputs: None
 */
class RandomFloat extends RandomOperator
{
    /**
     * Calculate a random floating point number in the given range
     *
     * @return float the calculated random float
     */
    protected function _simpleExecute()
    {
        $min_val = $this->parameters['min'];
        $max_val = $this->parameters['max'];
        return $this->_getUniform($min_val, $max_val);
    }
}
