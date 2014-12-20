<?php

use \Vimeo\ABLincoln\Namespaces\SimpleNamespace;
use \Vimeo\ABLincoln\Experiments\SimpleExperiment;
use \Vimeo\ABLincoln\Operators\Random as Random;

require_once __DIR__ . '/TestLogger.php';

/**
 * PHPUnit Namespace test class
 */
class NamespaceTest extends \PHPUnit_Framework_TestCase
{
    public function testVanillaNamespace()
    {
        $userid1 = 3;
        $username1 = 'user1';
        $userid2 = 7;
        $username2 = 'user2';
        $logger = new TestLogger();

        $namespace = new TestVanillaNamespace(
            array('userid' => $userid1, 'username' => $username1),
            $logger
        );
        $foo = $namespace->get('foo');
        $this->assertEquals($foo, 2);
        $this->assertEquals(count($logger->log), 1);

        $namespace = new TestVanillaNamespace(
            array('userid' => $userid2, 'username' => $username2),
            $logger
        );
        $foo = $namespace->get('foo');
        $this->assertEquals($foo, 'a');
        $this->assertEquals(count($logger->log), 2);

        $namespace->removeExperiment('first');
        $foo = $namespace->get('foo');
        $this->assertNull($foo);
    }
}

class TestVanillaNamespace extends SimpleNamespace
{
    public function setup()
    {
        $this->name = 'namespace_demo';
        $this->primary_unit = 'userid';
        $this->num_segments = 1000;
    }

    public function setupExperiments()
    {
        $this->addExperiment('first', 'TestExperiment', 300);
        $this->addExperiment('second', 'TestExperiment2', 700);
    }
}

class TestExperiment extends SimpleExperiment
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

class TestExperiment2 extends TestExperiment
{
    public function setup()
    {
        $this->name = 'test2_name';
    }

    public function assign($params, $inputs)
    {
        $params['foo'] = new Random\UniformChoice(
            array('choices' => array(1, 2, 3)),
            $inputs
        );
    }
}
