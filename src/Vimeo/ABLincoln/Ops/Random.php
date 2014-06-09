<?php

namespace Vimeo\ABLincoln\Ops;

class AbOpRandom extends AbOpSimple
{
    const LONG_SCALE = floatval(0xFFFFFFFFFFFFFFF);

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

    protected function getUnit($appended_unit = null)
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

    protected function getHash($appended_unit = null)
    {
        $salt = $this->parameters['salt'];
        $salty = "{$this->mapper['experiment_salt']}.$salt";
        $unit_str_arr = array_map('strval', $this->getUnit($appended_unit));
        $unit_str = implode('.', $unit_str_arr);
        return hexdec(substr(sha1("$salty.$unit_str"), 0, 15));
    }

    protected function getUniform($min_val = 0.0, $max_val = 1.0,
                                  $appended_unit = null)
    {
        $zero_to_one = $this->getHash(appended_unit) / self::LONG_SCALE;
        return $min_val + $zero_to_one * ($max_val - $min_val);
    }
}