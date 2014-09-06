<?php
namespace ActionTest;

use Phruts\Action\ExceptionHandler;

class ExceptionHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testInstantiate()
    {
        $object = new ExceptionHandler();
        $this->assertTrue($object instanceof \Phruts\Action\ExceptionHandler);
    }
}
 