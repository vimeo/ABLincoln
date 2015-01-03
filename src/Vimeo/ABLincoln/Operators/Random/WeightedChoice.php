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
        if (!array_key_exists('choices', $this->parameters) || !array_key_exists('weights', $this->parameters)) {
            throw new \Exception(get_class($this) . ": inputs 'choices' and 'weights' required.");
        }
        if (!is_array($this->parameters['choices']) || !is_array($this->parameters['weights'])) {
            throw new \Exception(get_class($this) . ": 'choices' and 'weights' must be arrays.");
        }

        $choices = array_values($this->parameters['choices']);
        $weights = array_values($this->parameters['weights']);

        if (count($choices) !== count($weights)) {
            throw new \Exception(get_class($this) . ": 'choices' and 'weights' must have the same length.");
        }
        if (count($choices) == 0 || count($weights) == 0) {
            throw new \Exception(get_class($this) . ": 'choices' and 'weights' must have at least one element.");
        }

        $non_numeric_weights = array_filter($weights, function($item) {
            return !is_numeric($item) || $item < 0.0;
        });
        if (count($non_numeric_weights) > 0) {
            throw new \Exception(get_class($this) . ": 'weights' must contain only non-negative numbers.");
        }

        // initialize array for making weighted draw
        $cum_sum = 0.0;
        $cum_weights = array();
        for ($i = 0; $i < count($choices); $i++) {
            $cum_sum += $weights[$i];
            $cum_weights[$i] = $cum_sum;
        }
        if ($cum_sum == 0) {
            throw new \Exception(get_class($this) . ": the sum of values in 'weights' must be positive.");
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
