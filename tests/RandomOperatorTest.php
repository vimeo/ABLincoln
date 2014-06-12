<?php

use \Vimeo\ABLincoln\Assignment;
use \Vimeo\ABLincoln\Operators\Random as Random;

/**
 * PHPUnit RandomOperator test class
 */
class RandomOperatorTest extends \PHPUnit_Framework_TestCase
{
    const Z = 3.29;  // z_(alpha/2) for alpha=.001, e.g. 99.9% CI: qnorm(1-(.001/2))

    /**
     * Convert a collection of value-mass pairs to value-density pairs
     *
     * @param array $value_mass array containing values and their respective frequencies
     * @return array array containing values and their respective densities
     */
    private static function valueMassToDensity($value_mass)
    {
        $mass_sum = floatval(array_sum($value_mass));
        $value_density = array();
        foreach ($value_mass as $value => $mass) {
            $value_density[$value] = $mass / $mass_sum;
        }
        return $value_density;
    }

    /**
     * Make sure an experiment object generates the desired frequencies
     *
     * @param function $func experiment object helper method
     * @param array $value_mass array containing values and their respective frequencies
     * @param int $N total number of outcomes
     */
    private function distributionTester($func, $value_mass, $N = 1000)
    {
        // run and store the results of $N trials of $func() with input $i
        $values = array();
        for ($i = 0; $i < $N; $i++) {
            $values[] = call_user_func($func, $i);
        }
        $value_density = self::valueMassToDensity($value_mass);

        // test outcome frequencies against expected density
        $this->assertProbs($values, $value_density, floatval($N));
    }

    /**
     * Check that a list of values has roughly the expected density
     *
     * @param array $values array containing all operator values
     * @param array $expected_density array mapping values to expected densities
     * @param float $N total number of outcomes
     */
    private function assertProbs($values, $expected_density, $N)
    {
        $hist = array_count_values($values);
        print_r($hist);
        foreach ($hist as $value => $value_sum) {
            $this->assertProp($value_sum / $N, $expected_density[$value], $N);
        }
    }

    /**
     * Test of proportions: normal approximation of binomial CI. This should be
     * OK for large N and values of p not too close to 0 or 1
     *
     * @param float $observed_p observed density of value
     * @param float $expected_p expected density of value
     * @param float $N total number of outcomes
     */
    private function assertProp($observed_p, $expected_p, $N)
    {
        $se = self::Z * sqrt($expected_p * (1 - $expected_p) / $N);
        $this->assertTrue(abs($observed_p - $expected_p) <= $se);
    }

    /**
     * Test BernoulliTrial random operator
     */
    public function testBernoulli()
    {
        BernoulliHelper::setArgs(array('p' => 0.0));
        $this->distributionTester('BernoulliHelper::execute', array(0 => 1, 1 => 0));
        BernoulliHelper::setArgs(array('p' => 0.1));
        $this->distributionTester('BernoulliHelper::execute', array(0 => 0.9, 1 => 0.1));
        BernoulliHelper::setArgs(array('p' => 1.0));
        $this->distributionTester('BernoulliHelper::execute', array(0 => 0, 1 => 1));
    }

    /**
     * Test UniformChoice random operator
     */
    public function testUniformChoice()
    {
        UniformHelper::setArgs(array('choices' => array('a')));
        $this->distributionTester('UniformHelper::execute', array('a' => 1));
        UniformHelper::setArgs(array('choices' => array('a', 'b')));
        $this->distributionTester('UniformHelper::execute', array('a' => 1, 'b' => 1));
        UniformHelper::setArgs(array('choices' => array(1, 2, 3, 4)));
        $this->distributionTester('UniformHelper::execute', array(1 => 1, 2 => 1, 3 => 1, 4 => 1));
    }

    /**
     * Test WeightedChoice random operator
     */
    public function testWeightedChoice()
    {
        $w = array('a' => 1);
        WeightedHelper::setArgs(array('choices' => array('a'), 'weights' => $w));
        $this->distributionTester('WeightedHelper::execute', $w);
        $w = array('a' => 1, 'b' => 2);
        WeightedHelper::setArgs(array('choices' => array('a', 'b'), 'weights' => $w));
        $this->distributionTester('WeightedHelper::execute', $w);
        $w = array('a' => 0, 'b' => 2, 'c' => 0);
        WeightedHelper::setArgs(array('choices' => array('a', 'b', 'c'), 'weights' => $w));
        $this->distributionTester('WeightedHelper::execute', $w);
    }
}

abstract class TestHelper
{
    protected static $args;
    public static function setArgs($args)
    {
        self::$args = $args;
    }
    abstract public static function execute($i);
}

class BernoulliHelper extends TestHelper
{
    public static function execute($i)
    {
        $a = new Assignment(self::$args['p']);
        $a['x'] = new Random\BernoulliTrial(array(
            'p' => self::$args['p'],
            'unit' => $i
        ));
        return $a['x'];
    }
}

class UniformHelper extends TestHelper
{
    public static function execute($i)
    {
        $a = new Assignment(implode(',', array_map('strval', self::$args['choices'])));
        $a['x'] = new Random\UniformChoice(array(
            'choices' => self::$args['choices'],
            'unit' => $i
        ));
        return $a['x'];
    }
}

class WeightedHelper extends TestHelper
{
    public static function execute($i)
    {
        $a = new Assignment(implode(',', array_map('strval', self::$args['choices'])));
        $a['x'] = new Random\WeightedChoice(array(
            'choices' => self::$args['choices'],
            'weights' => self::$args['weights'],
            'unit' => $i
        ));
        return $a['x'];
    }
}