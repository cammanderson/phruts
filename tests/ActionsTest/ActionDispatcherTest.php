<?php
namespace ActionsTest;

class ActionDispatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testInstantiate()
    {
        $actionDispatcher = new \Phruts\Actions\ActionDispatcher();
        $this->assertTrue($actionDispatcher instanceof \Phruts\Action);
    }
}
 