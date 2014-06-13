<?php

use \Vimeo\ABLincoln\Namespaces\SimpleNamespace;
use \Vimeo\ABLincoln\Experiments\AbstractExperiment;
use \Vimeo\ABLincoln\Operators\Random as Random;

$global_log = array();

/**
 * PHPUnit Namespace test class
 */
class NamespaceTest extends \PHPUnit_Framework_TestCase
{
    public function testVanillaNamespace()
    {
        global $global_log;
        $userid1 = 3;
        $username1 = 'user1';
        $userid2 = 7;
        $username2 = 'user2';

        $e = new TestVanillaNamespace(array(
            'userid' => $userid1,
            'username' => $username1
        ));
        $foo = $e->get('foo');
        $this->assertEquals($foo, 1);
        $this->assertEquals(count($global_log), 1);

        $e = new TestVanillaNamespace(array(
            'userid' => $userid2,
            'username' => $username2
        ));
        $foo = $e->get('foo');
        $this->assertEquals($foo, 'a');
        $this->assertEquals(count($global_log), 2);

        $e->removeExperiment('first');
        $foo = $e->get('foo');
        $this->assertNull($foo);
    }
}

class TestVanillaNamespace extends SimpleNamespace
{
    protected function setup()
    {
        $this->name = 'namespace_demo';
        $this->primary_unit = 'userid';
        $this->num_segments = 1000;
    }

    protected function setupExperiments()
    {
        $this->addExperiment('first', 'TestVanillaExperiment', 300);
        $this->addExperiment('second', 'TestVanillaExperiment2', 700);
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
        global $global_log;
        $global_log[] = $data;
    }
}

class TestVanillaExperiment2 extends TestVanillaExperiment
{
    protected function setup()
    {
        $this->name = 'test2_name';
    }

    protected function assign($params, $inputs)
    {
        $params['foo'] = new Random\UniformChoice(array(
            'choices' => array(1, 2, 3)
        ), $inputs);
    }
}