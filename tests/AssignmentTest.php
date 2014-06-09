<?php

use Vimeo\ABLincoln\Assignment;

class AssignmentTest extends \PHPUnit_Framework_TestCase
{
    private $tester_salt = 'test_salt';

    public function testOffsetSetUnset()
    {
        $a = new Assignment($this->tester_salt);
        $this->assertFalse(isset($a[0]));
        $a[0] = 5;
        $this->assertTrue(isset($a[0]));
        unset($a[0]);
        $this->assertFalse(isset($a[0]));
    }

    public function testSetGetConstant()
    {
        $a = new Assignment($this->tester_salt);
        $a[0] = 5;
        $this->assertEquals($a[0], 5);
    }

    public function testSetGetString()
    {
        $a = new Assignment($this->tester_salt);
        $a['test'] = 'a';
        $this->assertEquals($a['test'], 'a');
    }

    public function testSetGetNull()
    {
        $a = new Assignment($this->tester_salt);
        $a[12] = 5;
        $a[] = 'a';
        $this->assertEquals($a[13], 'a');
    }
}