<?php
namespace ActionsTest;

class CompactActionTest extends \PHPUnit_Framework_TestCase
{
    public function testInstantiate()
    {
        $compactAction = new \Phruts\Actions\CompactAction();
        $this->assertTrue($compactAction instanceof \Phruts\Action);
    }
}

 