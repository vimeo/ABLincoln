<?php

use \Vimeo\ABLincoln\Namespaces\SimpleNamespace;
use \Vimeo\ABLincoln\Experiments\AbstractExperiment;
use \Vimeo\ABLincoln\Operators\Random as Random;
use \Vimeo\ABLincoln\Experiments\Logging as Logging;

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

        $namespace = new TestVanillaNamespace(
            ['userid' => $userid1, 'username' => $username1]
        );
        $foo = $namespace->get('foo');
        $this->assertEquals($foo, 2);

        $namespace = new TestVanillaNamespace(
            ['userid' => $userid2, 'username' => $username2]
        );
        $foo = $namespace->get('foo');
        $this->assertEquals($foo, 'a');
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

class TestExperiment extends AbstractExperiment
{
    use Logging\PSRLoggerTrait;

    public function setup()
    {
        $this->name = 'test_name';
    }

    public function assign($params, $inputs)
    {
        $params['foo'] = new Random\UniformChoice(
            ['choices' => ['a', 'b']],
            $inputs
        );
    }
}

class TestExperiment2 extends AbstractExperiment
{
    use Logging\PSRLoggerTrait;

    public function setup()
    {
        $this->name = 'test2_name';
    }

    public function assign($params, $inputs)
    {
        $params['foo'] = new Random\UniformChoice(
            ['choices' => [1, 2, 3]],
            $inputs
        );
    }
}
