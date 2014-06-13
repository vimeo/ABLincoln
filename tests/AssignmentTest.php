<?php

use \Vimeo\ABLincoln\Assignment;

/**
 * PHPUnit Assignment test class
 */
class AssignmentTest extends \PHPUnit_Framework_TestCase
{
    private $_tester_salt = 'test_salt';

    /**
     * Test Assignment data set and unset functionality
     */
    public function testOffsetSetUnset()
    {
        $assignment = new Assignment($this->_tester_salt);
        $this->assertFalse(isset($assignment[0]));
        $assignment[0] = 5;
        $this->assertTrue(isset($assignment[0]));
        unset($assignment[0]);
        $this->assertFalse(isset($assignment[0]));
    }

    /**
     * Test Assignment array indexing at constant values
     */
    public function testSetGetConstant()
    {
        $assignment = new Assignment($this->_tester_salt);
        $assignment[0] = 5;
        $this->assertEquals($assignment[0], 5);
    }

    /**
     * Test Assignment array indexing at a string
     */
    public function testSetGetString()
    {
        $assignment = new Assignment($this->_tester_salt);
        $assignment['test'] = 'a';
        $this->assertEquals($assignment['test'], 'a');
    }

    /**
     * Test Assignment array setting at a null index (discouraged but works)
     */
    public function testSetGetNull()
    {
        $assignment = new Assignment($this->_tester_salt);
        $assignment[12] = 5;
        $assignment[] = 'a';
        $this->assertEquals($assignment[13], 'a');
    }
}
