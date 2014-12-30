<?php

namespace Vimeo\ABLincoln\Operators\Random;

use \Vimeo\ABLincoln\Operators\RandomOperator;

/**
 * Select a random sample from a given choices array
 *
 * Required Inputs:
 *   - 'choices': array of choices to sample
 * Optional Inputs:
 *   - 'draws': number of samples to draw
 */
class Sample extends RandomOperator
{
    /**
     * Choose a random sample of choices from the parameter array
     *
     * @return array the random sample select from the parameter array
     */
    protected function _simpleExecute()
    {
        $choices = array();
        foreach ($this->parameters['choices'] as $key => $value) {
            $choices[] = $value;
        }
        $num_choices = count($choices);
        $num_draws = isset($this->parameters['draws']) ? $this->parameters['draws'] : $num_choices;
        for ($i = $num_choices - 1; $i > 0; $i--) {
            $j = $this->_getHash($i) % ($i + 1);
            $temp = $choices[$i];
            $choices[$i] = $choices[$j];
            $choices[$j] = $temp;
        }
        return array_slice($choices, 0, $num_draws);
    }
}
