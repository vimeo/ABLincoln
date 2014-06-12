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
        var_dump($hist);
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
        BernoulliHelper::setP(0.0);
        $this->distributionTester('BernoulliHelper::execute', array(0 => 1, 1 => 0));
        BernoulliHelper::setP(0.1);
        $this->distributionTester('BernoulliHelper::execute', array(0 => 0.9, 1 => 0.1));
        BernoulliHelper::setP(1.0);
        $this->distributionTester('BernoulliHelper::execute', array(0 => 0, 1 => 1));
    }
}

class BernoulliHelper {
    private static $p;
    public static function setP($p) {
        self::$p = $p;
    }
    public static function execute($i) {
        $a = new Assignment(self::$p);
        $a['x'] = new Random\BernoulliTrial(array('p' => self::$p, 'unit' => $i));
        return $a['x'];
    }
}











