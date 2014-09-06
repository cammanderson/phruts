<?php
namespace ActionTest;

use Phruts\Action\ActionKernel;

class ActionKernelTest extends \PHPUnit_Framework_TestCase
{
    public function testInstantiate()
    {
        $object = new ActionKernel(new \Silex\Application());
        $this->assertTrue($object instanceof \Phruts\Action\ActionKernel);
    }
}
 