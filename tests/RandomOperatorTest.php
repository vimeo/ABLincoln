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
     * Make sure an experiment object generates the desired frequencies
     *
     * @param function $func experiment object helper method
     * @param array $value_mass array containing values and their respective frequencies
     * @param int $N total number of outcomes
     */
    private function listDistributionTester($func, $value_mass, $N = 1000)
    {
        // run and store the results of $N trials of $func() with input $i
        $values = array();
        for ($i = 0; $i < $N; $i++) {
            $values[] = call_user_func($func, $i);
        }
        $value_density = self::valueMassToDensity($value_mass);

        // transpose values array
        $rows = $N;
        $cols = count($values[0]);
        $values_trans = array();
        for ($i = 0; $i < $rows; $i++) {
            for ($j = 0; $j < $cols; $j++) {
                $values_trans[$j][$i] = $values[$i][$j];
            }
        }

        // test outcome frequencies against expected density. each $list is a 
        // row of the transpose of $values, and is expected to have the same
        // distribution as $value_density
        foreach ($values_trans as $key => $list) {
            $this->assertProbs($list, $value_density, floatval($N));
        }
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
     * Test RandomFloat random operator
     */
    public function testFloat()
    {
        $N = 5;
        $min = 0; $max = 1;
        FloatHelper::setArgs(array('min' => $min, 'max' => $max));
        for ($i = 0; $i < $N; $i++) {
            $f = FloatHelper::execute($i);
            $this->assertTrue($min <= $f && $f <= $max);
        }
        $min = 5; $max = 7;
        FloatHelper::setArgs(array('min' => $min, 'max' => $max));
        for ($i = 0; $i < $N; $i++) {
            $f = FloatHelper::execute($i);
            $this->assertTrue($min <= $f && $f <= $max);
        }
        $min = 2; $max = 2;
        FloatHelper::setArgs(array('min' => $min, 'max' => $max));
        for ($i = 0; $i < $N; $i++) {
            $f = FloatHelper::execute($i);
            $this->assertTrue($min <= $f && $f <= $max);
        }
    }

    /**
     * Test RandomInteger random operator
     */
    public function testInteger()
    {
        IntegerHelper::setArgs(array('min' => 0, 'max' => 1));
        $this->distributionTester('IntegerHelper::execute', array(0 => 1, 1 => 1));
        IntegerHelper::setArgs(array('min' => 5, 'max' => 7));
        $this->distributionTester('IntegerHelper::execute', array(5 => 1, 6 => 1, 7 => 1));
        IntegerHelper::setArgs(array('min' => 2, 'max' => 2));
        $this->distributionTester('IntegerHelper::execute', array(2 => 1));
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
        $weights = array('a' => 1);
        WeightedHelper::setArgs(array('choices' => array('a'), 'weights' => $weights));
        $this->distributionTester('WeightedHelper::execute', $weights);
        $weights = array('a' => 1, 'b' => 2);
        WeightedHelper::setArgs(array('choices' => array('a', 'b'), 'weights' => $weights));
        $this->distributionTester('WeightedHelper::execute', $weights);
        $weights = array('a' => 0, 'b' => 2, 'c' => 0);
        WeightedHelper::setArgs(array('choices' => array('a', 'b', 'c'), 'weights' => $weights));
        $this->distributionTester('WeightedHelper::execute', $weights);
    }

    /**
     * Test Sample random operator
     */
    public function testSample()
    {
        SampleHelper::setArgs(array('choices' => array(1, 2, 3), 'draws' => 3));
        $this->listDistributionTester('SampleHelper::execute', array(1 => 1, 2 => 1, 3 => 1));
        SampleHelper::setArgs(array('choices' => array(1, 2, 3), 'draws' => 2));
        $this->listDistributionTester('SampleHelper::execute', array(1 => 1, 2 => 1, 3 => 1));
        SampleHelper::setArgs(array('choices' => array('a', 'a', 'b'), 'draws' => 3));
        $this->listDistributionTester('SampleHelper::execute', array('a' => 2, 'b' => 1));
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

class FloatHelper extends TestHelper
{
    public static function execute($i)
    {
        $exp_salt = sprintf('%s,%s', strval(self::$args['min']), strval(self::$args['max']));
        $assignment = new Assignment($exp_salt);
        $assignment['x'] = new Random\RandomFloat(array(
            'min' => self::$args['min'],
            'max' => self::$args['max'],
        ), array('unit' => $i));
        return $assignment['x'];
    }
}

class IntegerHelper extends TestHelper
{
    public static function execute($i)
    {
        $exp_salt = sprintf('%s,%s', strval(self::$args['min']), strval(self::$args['max']));
        $assignment = new Assignment($exp_salt);
        $assignment['x'] = new Random\RandomInteger(array(
            'min' => self::$args['min'],
            'max' => self::$args['max'],
        ), array('unit' => $i));
        return $assignment['x'];
    }
}

class BernoulliHelper extends TestHelper
{
    public static function execute($i)
    {
        $assignment = new Assignment(self::$args['p']);
        $assignment['x'] = new Random\BernoulliTrial(array(
            'p' => self::$args['p'],
        ), array('unit' => $i));
        return $assignment['x'];
    }
}

class UniformHelper extends TestHelper
{
    public static function execute($i)
    {
        $assignment = new Assignment(implode(',', array_map('strval', self::$args['choices'])));
        $assignment['x'] = new Random\UniformChoice(array(
            'choices' => self::$args['choices'],
        ), array('unit' => $i));
        return $assignment['x'];
    }
}

class WeightedHelper extends TestHelper
{
    public static function execute($i)
    {
        $assignment = new Assignment(implode(',', array_map('strval', self::$args['choices'])));
        $assignment['x'] = new Random\WeightedChoice(array(
            'choices' => self::$args['choices'],
            'weights' => self::$args['weights'],
        ), array('unit' => $i));
        return $assignment['x'];
    }
}

class SampleHelper extends TestHelper
{
    public static function execute($i)
    {
        $assignment = new Assignment(implode(',', array_map('strval', self::$args['choices'])));
        $assignment['x'] = new Random\Sample(array(
            'choices' => self::$args['choices'],
            'draws' => self::$args['draws'],
        ), array('unit' => $i));
        return $assignment['x'];
    }
}