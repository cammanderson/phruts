<?php
namespace ActionsTest;

class SwitchActionTest extends \PHPUnit_Framework_TestCase
{
    public function testInstantiate()
    {
        $action = new \Phruts\Actions\SwitchAction();
        $this->assertTrue($action instanceof \Phruts\Action);
    }
}
 