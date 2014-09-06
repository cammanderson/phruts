<?php
namespace ActionsTest;

class LookupDispatchActionTest extends \PHPUnit_Framework_TestCase
{
    public function testInstantiate()
    {
        $action = new \Phruts\Actions\LookupDispatchAction();
        $this->assertTrue($action instanceof \Phruts\Action);
    }
}
 