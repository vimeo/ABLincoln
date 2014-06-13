<?php

use \Vimeo\ABLincoln\Experiments\AbstractExperiment;
use \Vimeo\ABLincoln\Operators\Random as Random;

/**
 * PHPUnit Experiment test class
 */
class ExperimentTest extends \PHPUnit_Framework_TestCase
{
    public static $log = array();

    public function testVanillaExperiment()
    {
        $userid = 42;
        $username = 'a_name';

        $e = new TestVanillaExperiment(array(
            'userid' => $userid
        ));
        $params = $e->getParams();
        $this->assertTrue(array_key_exists('foo', $params));
        $this->assertEquals($params['foo'], 'b');
        $this->assertEquals(count(self::$log), 1);

        $e = new TestVanillaExperiment(array(
            'userid' => $userid,
            'username' => $username
        ));
        $params = $e->getParams();
        $this->assertTrue(array_key_exists('foo', $params));
        $this->assertEquals($params['foo'], 'a');
        $this->assertEquals(count(self::$log), 2);
    }
}

class TestVanillaExperiment extends AbstractExperiment
{
    protected function setup()
    {
        $this->name = 'test_name';
    }

    protected function assign($params, $inputs)
    {
        $params['foo'] = new Random\UniformChoice(array(
            'choices' => array('a', 'b')
        ), $inputs);
    }

    protected function previouslyLogged()
    {
        return false;
    }

    protected function configureLogger() {}

    protected function log($data)
    {
        ExperimentTest::$log[] = $data;
    }
}