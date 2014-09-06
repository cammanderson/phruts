<?php
namespace ActionsTest;

class ForwardActionTest extends \PHPUnit_Framework_TestCase
{
    public function testInstantiate()
    {
        $action = new \Phruts\Actions\ForwardAction();
        $this->assertTrue($action instanceof \Phruts\Action);
    }
}
 