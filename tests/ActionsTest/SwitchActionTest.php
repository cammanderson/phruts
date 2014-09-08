<?php
namespace ActionsTest;

use Phruts\Config\ActionConfig;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SwitchActionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Phruts\Action
     */
    protected $action;
    protected $mapping;
    protected $request;
    protected $response;

    public function setUp()
    {
        $internal = $this->getMockBuilder('\Phruts\Util\MessageResources')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $internal->expects($this->any())
            ->method('getMessage')
            ->willReturn('message')
        ;

        $actionKernel = $this->getMockBuilder('\Phruts\Action\ActionKernel')
            ->disableOriginalConstructor()
            ->getMock();

        $actionKernel
            ->expects($this->any())
            ->method('getInternal')
            ->willReturn($internal);

        $this->action = new \Phruts\Actions\SwitchAction();
        $this->action->setActionKernel($actionKernel);
        $this->mapping = new ActionConfig();
        $this->request = new Request();
        $this->response = new Response();
    }

    public function testInstantiate()
    {
        $this->assertTrue($this->action instanceof \Phruts\Action);
    }

    public function testEmptyExecute()
    {
        $this->setExpectedException('\Phruts\Exception');
        $this->action->execute($this->mapping, null, $this->request, $this->response);
    }

}
 