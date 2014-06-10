<?php

namespace Vimeo\ABLincoln\Ops\Random;
use Vimeo\ABLincoln\Ops\Base\OpRandom;

/**
 * Random operator used to calculate pseudorandom floating point numbers
 */
class RandomFloat extends OpRandom
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
                'description' => 'min (float) value drawn'
            ),
            'max' => array(
                'required' => 1,
                'description' => 'max (float) value drawn'
            )
        );
    }

    /**
     * Calculate a random floating point number in the given range
     *
     * @return float the calculated random float
     */
    protected function simpleExecute()
    {
        $min_val = $this->parameters['min'];
        $max_val = $this->parameters['max'];
        return $this->getUniform($min_val, $max_val);
    }
}