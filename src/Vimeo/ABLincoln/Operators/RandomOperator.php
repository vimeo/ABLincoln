<?php

namespace Vimeo\ABLincoln\Operators;

/**
 * Base class for random operators.
 *
 * Required Inputs: None
 * Optional Inputs:
 *   - 'salt': salt for hash (should generally be unique for each random
 *         variable). If 'salt' input not specified, parameter name is used as
 *         random variable salt.
 */
abstract class RandomOperator extends AbstractSimpleOperator
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
     * Format all units into an array before hashing
     *
     * @param mixed $appended_unit optional extra unit used for hashing
     * @return array array of units used for hashing
     */
    private function _getUnit($appended_unit = null)
    {
        $unit = $this->parameters['unit'];
        if (!is_array($unit)) {
            $unit = [$unit];
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
    protected function _getHash($appended_unit = null)
    {
        $salt = $this->parameters['salt'];
        $salty = $this->mapper['experiment_salt'] . '.' . $salt;
        $unit_str_arr = array_map('strval', $this->_getUnit($appended_unit));
        $unit_str = implode('.', $unit_str_arr);
        return hexdec(substr(sha1($salty . '.' . $unit_str), 0, 15));
    }

    /**
     * Get a random decimal between two provided values
     *
     * @param float $min_value start value for random number range
     * @param float $max_value end value for random number range
     * @return float random number between the two provided values
     */
    protected function _getUniform($min_val = 0.0, $max_val = 1.0,
                                  $appended_unit = null)
    {
        $zero_to_one = $this->_getHash($appended_unit) / $this->long_scale;
        return $min_val + $zero_to_one * ($max_val - $min_val);
    }
}
