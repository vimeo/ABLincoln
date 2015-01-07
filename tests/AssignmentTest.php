<?php

use \Vimeo\ABLincoln\Assignment;

/**
 * PHPUnit Assignment test class
 */
class AssignmentTest extends \PHPUnit_Framework_TestCase
{
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
     * Test Assignment array indexing at constant values
     */
    public function testSetGetConstant()
    {
        $assignment = new Assignment($this->tester_salt);
        $assignment->foo = 5;
        $this->assertEquals($assignment->foo, 5);
    }

    /**
     * Test Assignment array indexing at a string
     */
    public function testSetGetString()
    {
        $assignment = new Assignment($this->tester_salt);
        $assignment->foo = 'bar';
        $this->assertEquals($assignment->foo, 'bar');
    }
}
