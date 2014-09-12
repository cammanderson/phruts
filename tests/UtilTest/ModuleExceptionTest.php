<?php
namespace Phruts\Util;

use Phruts\Action\ActionMessage;

class ModuleExceptionTest  extends \PHPUnit_Framework_TestCase
{

    public function testInstantiation()
    {

        $actionMessage = new ActionMessage('val');
        $exception = new ModuleException($actionMessage, 0, new \Exception('message'));
        $this->assertNotEmpty($exception);

        $actionMessage2 = new ActionMessage('val2');
        $exception->setActionMessage($actionMessage2);
        $this->assertEquals('val2', $exception->getActionMessage()->getKey());
        $exception->setProperty('property');
        $this->assertEquals('property', $exception->getProperty());
    }
}
 