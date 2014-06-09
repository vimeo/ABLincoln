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

class RandomFloat extends AbOpRandom
{
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

    protected function simpleExecute()
    {
        $min_val = $this->parameters['min'];
        $max_val = $this->parameters['max'];
        return $this->getUniform($min_val, $max_val);
    }
}

class RandomInteger extends AbOpRandom
{
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

    protected function simpleExecute()
    {
        $min_val = $this->parameters['min'];
        $max_val = $this->parameters['max'];
        return $min_val + $this->getHash() % ($max_val - $min_val + 1);
    }
}

class BernoulliTrial extends AbOpRandom
{
    public function options()
    {
        return array(
            'p' => array(
                'required' => 1,
                'description' => 'probability of drawing 1'
            )
        );
    }

    protected function simpleExecute()
    {
        $p = $this->parameters['p'];
        $rand_val = $this->getUniform(0.0, 1.0);
        return ($rand_val <= $p) ? 1 : 0;
    }    
}

class BernoulliFilter extends AbOpRandom
{
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

    public simpleExecute()
    {
        $p = $this->parameters['p'];
        $choices = $this->parameters['choices'];
        $num_choices = count($choices);
        if (!$num_choices) {
            return array()
        }
        return $choices[$this->getHash() % $num_choices];
    }
}