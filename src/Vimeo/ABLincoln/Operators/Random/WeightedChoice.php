<?php

namespace Vimeo\ABLincoln\Operators\Random;

use \Vimeo\ABLincoln\Operators\RandomOperator;

/**
 * Select an element from a choices array according to given probabilities
 *
 * Required Inputs:
 *   - 'choices': array of elements to draw from
 *   - 'weights': array of draw probabilities
 * Optional Inputs: None
 */
class WeightedChoice extends RandomOperator
{
    /**
     * Choose an element with weighted probability from the parameter array
     *
     * @return mixed the element chosen from the given array
     */
    protected function _simpleExecute()
    {
        $choices = array_values($this->parameters['choices']);
        $weights = array_values($this->parameters['weights']);
        if (empty($choices)) {
            return array();
        }

        // initialize array for making weighted draw
        $cum_sum = 0.0;
        $cum_weights = array();
        for ($i = 0; $i < count($choices); $i++) {
            $cum_sum += $weights[$i];
            $cum_weights[$i] = $cum_sum;
        }

        // find first choice where cumulative weight is > stopping value
        $stop_value = $this->_getUniform(0.0, $cum_sum);
        for ($i = 0; $i < count($choices); $i++) {
            if ($stop_value <= $cum_weights[$i] && $cum_weights[$i] > 0) {
                return $choices[$i];
            }
        }
    }
}
