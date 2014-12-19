<?php

use \Vimeo\ABLincoln\Experiments\SimpleExperiment;
use \Vimeo\ABLincoln\Operators\Random as Random;
use \Psr\Log\AbstractLogger;

require_once __DIR__ . '/TestLogger.php';

/**
 * PHPUnit Experiment test class
 */
class ExperimentTest extends \PHPUnit_Framework_TestCase
{
    public function testVanillaExperiment()
    {
        $userid = 42;
        $username = 'a_name';
        $logger = new TestLogger();

        $experiment = new TestVanillaExperiment(
            array('userid' => $userid),
            $logger
        );
        $params = $experiment->getParams();
        $this->assertTrue(array_key_exists('foo', $params));
        $this->assertEquals($params['foo'], 'b');
        $this->assertEquals(count($logger->log), 1);

        $experiment = new TestVanillaExperiment(
            array('userid' => $userid, 'username' => $username),
            $logger
        );
        $params = $experiment->getParams();
        $this->assertTrue(array_key_exists('foo', $params));
        $this->assertEquals($params['foo'], 'a');
        $this->assertEquals(count($logger->log), 2);
    }
}

class TestVanillaExperiment extends SimpleExperiment
{
    public function setup()
    {
        $this->name = 'test_name';
    }

    public function assign($params, $inputs)
    {
        $params['foo'] = new Random\UniformChoice(
            array('choices' => array('a', 'b')),
            $inputs
        );
    }

    protected function _previouslyLogged()
    {
        return false;
    }
}
