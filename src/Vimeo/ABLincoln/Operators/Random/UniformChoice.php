<?php

namespace Vimeo\ABLincoln\Operators\Random;

use \Vimeo\ABLincoln\Operators\RandomOperator;

/**
 * Randomly select a choice from an array of options
 */
class UniformChoice extends RandomOperator
{
    /**
     * The operator requires an array of choices to draw from
     *
     * @return array the array of required parameters
     */
    public function options()
    {
        return array(
            'choices' => array(
                'required' => 1,
                'description' => 'elements to draw from'
            )
        );
    }

    /**
     * Choose an element randomly from the parameter array
     *
     * @return mixed the element chosen from the given array
     */
    protected function simpleExecute()
    {
        $choices = $this->parameters['choices'];
        $num_choices = count($choices);
        if (!$num_choices) {
            return array();
        }
        return $choices[$this->getHash() % $num_choices];
    }
}