<?php

namespace Vimeo\ABLincoln\Operators\Random;

use \Vimeo\ABLincoln\Operators\RandomOperator;

/**
 * Select an element from a choices array according to given probabilities
 */
class WeightedChoice extends RandomOperator
{
    /**
     * The operator requires an set of choices to draw from and weights to use
     *
     * @return array the array of required parameters
     */
    public function options()
    {
        return array(
            'choices' => array(
                'required' => 1,
                'description' => 'elements to draw from'
            ),
            'weights' => array(
                'required' => 1,
                'description' => 'probability of draw'
            )
        );
    }

    /**
     * Choose an element with weighted probability from the parameter array
     *
     * @return mixed the element chosen from the given array
     */
    protected function simpleExecute()
    {
        $choices = $this->parameters['choices'];
        $weights = $this->parameters['weights'];
        if (!count($choices)) {
            return array();
        }
        $cum_weights = array_combine($choices, $weights);
        $cum_sum = 0.0;
        foreach ($cum_weights as $choice => $weight) {
            $cum_sum += $weight;
            $cum_weights[$choice] = $cum_sum;
        }
        $stop_value = $this->getUniform(0.0, $cum_sum);
        foreach ($cum_weights as $choice => $weight) {
            if ($stop_value <= $weight) {
                return $choice;
            }
        }
    }
}