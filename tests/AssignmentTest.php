<?php

use \Vimeo\ABLincoln\Assignment;
use \Vimeo\ABLincoln\Operators\Random as Random;

/**
 * PHPUnit Assignment test class
 */
class AssignmentTest extends \PHPUnit_Framework_TestCase
{
    private $tester_unit = 4;
    private $tester_salt = 'test_salt';

    /**
     * Test Assignment data set and unset functionality
     */
    public function testOffsetSetUnset()
    {
        $assignment = new Assignment($this->tester_salt);
        $this->assertFalse(isset($assignment->foo));
        $assignment->foo = 5;
        $this->assertTrue(isset($assignment->foo));
        unset($assignment->foo);
        $this->assertFalse(isset($assignment->foo));
    }

    /**
     * Test Assignment indexing at constant values
     */
    public function testSetGetConstants()
    {
        $assignment = new Assignment($this->tester_salt);
        $assignment->foo = 12;
        $assignment->bar = 'baz';
        $this->assertEquals($assignment->foo, 12);
        $this->assertEquals($assignment->bar, 'baz');
    }

    /**
     * Test Assignment RandomOperator setting using UniformChoice
     */
    public function testSetGetUniform()
    {
        $assignment = new Assignment($this->tester_salt);
        $assignment->foo = new Random\UniformChoice(
            ['choices' => ['a', 'b']],
            ['unit' => $this->tester_unit]
        );
        $assignment->bar = new Random\UniformChoice(
            ['choices' => ['a', 'b']],
            ['unit' => $this->tester_unit]
        );
        $assignment->baz = new Random\UniformChoice(
            ['choices' => ['a', 'b']],
            ['unit' => $this->tester_unit]
        );

        $this->assertEquals($assignment->foo, 'b');
        $this->assertEquals($assignment->bar, 'a');
        $this->assertEquals($assignment->baz, 'a');
    }

    /**
     * Test Assignment override functionality
     */
    public function testOverrides()
    {
        $assignment = new Assignment($this->tester_salt);
        $assignment->setOverrides(['x' => 42, 'y' => 43]);
        $assignment->x = 5;
        $assignment->y = 6;
        $this->assertEquals($assignment->x, 42);
        $this->assertEquals($assignment->y, 43);
    }
}
