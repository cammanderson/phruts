<?php

class ActionTest extends \PHPUnit_Framework_TestCase
{
    public function testMutatorAccessors()
    {
        $action = new \Phruts\Action();

        $mockActionKernel = new MockActionKernel();
        $action->setActionKernel($mockActionKernel);
        $this->assertNotEmpty($action->getActionKernel());
    }
}


class MockActionKernel extends \Phruts\Action\ActionKernel
{

    function __construct()
    {
    }
}