<?php

namespace Vimeo\ABLincoln\Ops\Random;
use Vimeo\ABLincoln\Ops\Base\OpRandom;

/**
 * Filter an array with Bernoulli Trial probability for each element
 */
class BernoulliFilter extends OpRandom
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
    protected function simpleExecute()
    {
        $p = $this->parameters['p'];
        $choices = $this->parameters['choices'];
        $num_choices = count($choices);
        if (!$num_choices) {
            return array();
        }
        return array_filter($choices, function($item) use ($p) {
            return $this->getUniform(0.0, 1.0, $item) <= $p;
        });
    }
}