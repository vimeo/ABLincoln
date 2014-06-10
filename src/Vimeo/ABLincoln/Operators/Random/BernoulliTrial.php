<?php

namespace Vimeo\ABLincoln\Operators\Random;

use \Vimeo\ABLincoln\Operators\RandomOperator;

/**
 * Simulate a Bernoulli Trial by choosing 1 or 0 with a given probability
 */
class BernoulliTrial extends RandomOperator
{
    /**
     * The operator requires a probability value to run
     *
     * @return array the array of required parameters
     */
    public function options()
    {
        return array(
            'p' => array(
                'required' => 1,
                'description' => 'probability of drawing 1'
            )
        );
    }

    /**
     * Calculate either 1 or 0 with a given probability
     *
     * @return int 1 with probability p, 0 otherwise
     */
    protected function simpleExecute()
    {
        $p = $this->parameters['p'];
        $rand_val = $this->getUniform(0.0, 1.0);
        return ($rand_val <= $p) ? 1 : 0;
    }    
}