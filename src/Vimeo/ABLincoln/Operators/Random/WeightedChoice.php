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
        $choices = $this->parameters['choices'];
        $weights = $this->parameters['weights'];
        if (empty($choices)) {
            return array();
        }
        $cum_weights = array_combine($choices, $weights);
        $cum_sum = 0.0;
        foreach ($cum_weights as $choice => $weight) {
            $cum_sum += $weight;
            $cum_weights[$choice] = $cum_sum;
        }
        $stop_value = $this->_getUniform(0.0, $cum_sum);
        foreach ($cum_weights as $choice => $weight) {
            if ($stop_value <= $weight) {
                return $choice;
            }
        }
    }
}
