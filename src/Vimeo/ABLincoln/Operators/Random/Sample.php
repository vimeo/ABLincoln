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
        if (!array_key_exists('choices', $this->parameters)) {
            throw new \Exception(get_class($this) . ": input 'choices' required.");
        }
        if (!is_array($this->parameters['choices'])) {
            throw new \Exception(get_class($this) . ": 'choices' must be an array.");
        }

        $choices = array_values($this->parameters['choices']);
        $num_choices = count($choices);

        if (array_key_exists('draws', $this->parameters)) {
            if (!is_numeric($this->parameters['draws'])) {
                throw new \Exception(get_class($this) . ": if given, 'draws' must be a numeric integer value.");
            }
            if ($this->parameters['draws'] > $num_choices) {
                throw new \Exception(get_class($this) . ": cannot make more draws than there are choices available.");
            }
            $num_draws = $this->parameters['draws'];
        }
        else {
            $num_draws = $num_choices;
        }

        for ($i = $num_choices - 1; $i > 0; $i--) {
            $j = $this->_getHash($i) % ($i + 1);
            $temp = $choices[$i];
            $choices[$i] = $choices[$j];
            $choices[$j] = $temp;
        }
        return array_slice($choices, 0, $num_draws);
    }
}
