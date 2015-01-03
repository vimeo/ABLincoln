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
        if (!array_key_exists('min', $this->parameters) || !array_key_exists('max', $this->parameters)) {
            throw new \Exception(get_class($this) . ": inputs 'min' and 'max' required.");
        }

        $min_val = $this->parameters['min'];
        $max_val = $this->parameters['max'];

        if (!is_numeric($min_val) || !is_numeric($max_val)) {
            throw new \Exception(get_class($this) . ": 'min' and 'max' must both be numeric integer values.");
        }

        return $min_val + $this->_getHash() % ($max_val - $min_val + 1);
    }
}
