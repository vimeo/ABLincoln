<?php

namespace Vimeo\ABLincoln\Operators\Random;

use \Vimeo\ABLincoln\Operators\RandomOperator;

/**
 * Select a random sample from a given choices array
 */
class Sample extends RandomOperator
{
    /**
     * The operator requires an set of choices to draw from and number to draw
     *
     * @return array the array of required parameters
     */
    public function options()
    {
        return array(
            'choices' => array(
                'required' => 1,
                'description' => 'choices to sample'
            ),
            'draws' => array(
                'required' => 0,
                'description' => 'number of samples to draw'
            )
        );
    }

    /**
     * Choose a random sample of choices from the parameter array
     *
     * @return array the random sample select from the parameter array
     */
    protected function simpleExecute()
    {
        $choices = array();
        foreach ($this->parameters['choices'] as $key) {
            $choices[] = $key;
        }
        $num_choices = count($choices);
        $num_draws = isset($this->parameters['draws']) ? $this->parameters['draws']
                                                       : $num_choices;
        for ($i = $num_choices - 1; $i > 0; $i--) {
            $j = $this->getHash($i) % ($i + 1);
            $temp = $choices[i];
            $choices[i] = $choices[j];
            $choices[j] = $temp;
        }
    }
}