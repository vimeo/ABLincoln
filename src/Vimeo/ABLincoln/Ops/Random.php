<?php

namespace Vimeo\ABLincoln\Ops;

/**
 * Base class for random operators.
 */
class AbOpRandom extends AbOpSimple
{
    private $long_scale;

    /**
     * Constructor: store given parameters and establish scale for hashing
     *
     * @param array $parameters array mapping operator parameters to values
     */
    public function __construct($parameters)
    {
        parent::__construct($parameters);
        $this->long_scale = floatval(0xFFFFFFFFFFFFFFF);
    }

    /**
     * All random operators require a unit to hash on and an optional salt
     *
     * @return array the array of required parameters
     */
    public function options()
    {
        return array(
            'unit' => array(
                'required' => 1,
                'description' => 'unit to hash on'
            ),
            'salt' => array(
                'required' => 0,
                'description' => 'salt for hash. should generally be unique for each random variable. if not specified parameter name is used'
            )
        );
    }


    /**
     * Format all units into an array before hashing
     *
     * @param mixed $appended_unit optional extra unit used for hashing
     * @return array array of units used for hashing
     */
    private function getUnit($appended_unit = null)
    {
        $unit = $this->parameters['unit'];
        if (!is_array($unit)) {
            $unit = array($unit);
        }
        if (!is_null($appended_unit)) {
            $unit[] = $appended_unit;
        }
        return unit;
    }

    /**
     * Form a complete salt string and hash it to a number
     *
     * @param mixed $appended_unit optional extra unit used for hashing
     * @return int decimal representation of computed SHA1 hash
     */
    protected function getHash($appended_unit = null)
    {
        $salt = $this->parameters['salt'];
        $salty = "{$this->mapper['experiment_salt']}.$salt";
        $unit_str_arr = array_map('strval', $this->getUnit($appended_unit));
        $unit_str = implode('.', $unit_str_arr);
        return hexdec(substr(sha1("$salty.$unit_str"), 0, 15));
    }

    /**
     * Get a random decimal between two provided values
     *
     * @param float $min_value start value for random number range
     * @param float $max_value end value for random number range
     * @return float random number between the two provided values
     */
    protected function getUniform($min_val = 0.0, $max_val = 1.0,
                                  $appended_unit = null)
    {
        $zero_to_one = $this->getHash($appended_unit) / $this->long_scale;
        return $min_val + $zero_to_one * ($max_val - $min_val);
    }
}

/**
 * Random operator used to calculate pseudorandom floating point numbers
 */
class RandomFloat extends AbOpRandom
{
    /**
     * The operator requires a minimum and maximum value for the range
     *
     * @return array the array of required parameters
     */
    public function options()
    {
        return array(
            'min' => array(
                'required' => 1,
                'description' => 'min (float) value drawn'
            ),
            'max' => array(
                'required' => 1,
                'description' => 'max (float) value drawn'
            )
        );
    }

    /**
     * Calculate a random floating point number in the given range
     *
     * @return float the calculated random float
     */
    protected function simpleExecute()
    {
        $min_val = $this->parameters['min'];
        $max_val = $this->parameters['max'];
        return $this->getUniform($min_val, $max_val);
    }
}

/**
 * Random operator used to calculate pseudorandom integers
 */
class RandomInteger extends AbOpRandom
{
    /**
     * The operator requires a minimum and maximum value for the range
     *
     * @return array the array of required parameters
     */
    public function options()
    {
        return array(
            'min' => array(
                'required' => 1,
                'description' => 'min (int) value drawn'
            ),
            'max' => array(
                'required' => 1,
                'description' => 'max (int) value drawn'
            )
        );
    }

    /**
     * Calculate a random integer in the given range
     *
     * @return int the calculated random integer
     */
    protected function simpleExecute()
    {
        $min_val = $this->parameters['min'];
        $max_val = $this->parameters['max'];
        return $min_val + $this->getHash() % ($max_val - $min_val + 1);
    }
}

/**
 * Simulate a Bernoulli Trial by choosing 1 or 0 with a given probability
 */
class BernoulliTrial extends AbOpRandom
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

/**
 * Filter an array with Bernoulli Trial probability for each element
 */
class BernoulliFilter extends AbOpRandom
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

/**
 * Randomly select a choice from an array of options
 */
class UniformChoice extends AbOpRandom
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

/**
 * Select an element from a choices array according to given probabilities
 */
class WeightedChoice extends AbOpRandom
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

/**
 * Select a random sample from a given choices array
 */
class Sample extends AbOpRandom
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