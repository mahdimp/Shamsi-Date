<?php
require 'Date.php';

class DateTest extends PHPUnit_Framework_TestCase
{
    protected $_jalali;

    public function setUp()
    {
        $this->_jalali = new \Jalali\Date();
    }

    public function testDateFunc()
    {
        //$this->markTestSkipped();
        $result = \Jalali\date('l S F Y H:i:s a T');
        var_dump($result);
    }

    public function testDate()
    {
        //$this->markTestSkipped();
        $result = $this->_jalali->date('l S F Y H:i:s a T');
        var_dump($result);
    }
}
