<?php

namespace Vimeo\ABLincoln\Operators\Random;

use \Vimeo\ABLincoln\Operators\RandomOperator;

/**
 * Filter an array with Bernoulli Trial probability for each element
 */
class BernoulliFilter extends RandomOperator
{
    /**
     * The operator requires a probability value and choices array to filter
     *
     * @return array the array of required parameters
     */
    public function options()
    {
        return array(
            'p' => array(
                'required' => 1,
                'description' => 'probability of retaining element'
            ),
            'choices' => array(
                'required' => 1,
                'description' => 'elements being filtered'
            )
        );
    }

    /**
     * Filter the parameter array on each element with probability p
     *
     * @return array the filtered choices array
     */
    protected function _simpleExecute()
    {
        $p = $this->_parameters['p'];
        $choices = $this->_parameters['choices'];
        $num_choices = count($choices);
        if (!$num_choices) {
            return array();
        }
        return array_filter($choices, function($item) use ($p) {
            return $this->_getUniform(0.0, 1.0, $item) <= $p;
        });
    }
}