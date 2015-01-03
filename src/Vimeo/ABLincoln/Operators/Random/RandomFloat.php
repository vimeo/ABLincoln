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
        if (!array_key_exists('min', $this->parameters) || !array_key_exists('max', $this->parameters)) {
            throw new \Exception(get_class($this) . ": inputs 'min' and 'max' required.");
        }

        $min_val = $this->parameters['min'];
        $max_val = $this->parameters['max'];

        if (!is_numeric($min_val) || !is_numeric($max_val)) {
            throw new \Exception(get_class($this) . ": 'min' and 'max' must both be numeric values.");
        }

        return $this->_getUniform($min_val, $max_val);
    }
}
