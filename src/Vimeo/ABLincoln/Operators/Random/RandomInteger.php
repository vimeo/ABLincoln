<?php

namespace Vimeo\ABLincoln\Operators\Random;

use \Vimeo\ABLincoln\Operators\RandomOperator;

/**
 * Random operator used to calculate pseudorandom integers
 */
class RandomInteger extends RandomOperator
{
    /**
     * The operator requires a minimum and maximum value for the range
     *
     * @return array the array of required parameters
     */
    public function options()
    {
        return array(
            'min' => array(
                'required' => 1,
                'description' => 'min (int) value drawn'
            ),
            'max' => array(
                'required' => 1,
                'description' => 'max (int) value drawn'
            )
        );
    }

    /**
     * Calculate a random integer in the given range
     *
     * @return int the calculated random integer
     */
    protected function simpleExecute()
    {
        $min_val = $this->parameters['min'];
        $max_val = $this->parameters['max'];
        return $min_val + $this->getHash() % ($max_val - $min_val + 1);
    }
}