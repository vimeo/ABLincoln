<?php

namespace Vimeo\ABLincoln\Operators;

/**
 * Base class for random operators.
 */
abstract class RandomOperator extends AbstractOperator
{
    private $long_scale;

    /**
     * Constructor: store given parameters and establish scale for hashing
     *
     * @param array $options array mapping operator options to values
     * @param mixed $inputs input value/array used for hashing
     */
    public function __construct($options, $inputs)
    {
        parent::__construct($options, $inputs);
        $this->long_scale = floatval(0xFFFFFFFFFFFFFFF);
    }

    /**
     * All random operators take an optional salt that replaces the parameter
     * name for hashing if it is set
     *
     * @return array the array of required parameters
     */
    public function options()
    {
        return array(
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
        return $unit;
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