<?php

use \Vimeo\ABLincoln\Experiments\AbstractExperiment;
use \Vimeo\ABLincoln\Operators\Random as Random;
use \Vimeo\ABLincoln\Experiments\Logging as Logging;

require_once 'TestLogger.php';

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
            array('userid' => $userid)
        );
        $experiment->setLogger($logger);
        $params = $experiment->getParams();
        $this->assertTrue(array_key_exists('foo', $params));
        $this->assertEquals($params['foo'], 'b');
        $this->assertEquals(count($logger->log), 1);

        $experiment = new TestVanillaExperiment(
            array('userid' => $userid, 'username' => $username)
        );
        $experiment->setLogger($logger);
        $params = $experiment->getParams();
        $this->assertTrue(array_key_exists('foo', $params));
        $this->assertEquals($params['foo'], 'a');
        $this->assertEquals(count($logger->log), 2);
    }
}

class TestVanillaExperiment extends AbstractExperiment
{
    use Logging\PSRLoggerTrait;

    public function setup()
    {
        $this->name = 'test_name';
        $this->setLogLevel('debug');
    }

    public function assign($params, $inputs)
    {
        $params['foo'] = new Random\UniformChoice(
            array('choices' => array('a', 'b')),
            $inputs
        );
    }
}
