<?php
namespace ActionsTest;

use Phruts\Action\ActionMapping;
use Phruts\Actions\ActionDispatcher;
use Phruts\Config\ActionConfig;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ActionDispatcherTest extends \PHPUnit_Framework_TestCase
{

    protected $actionDispatcher;
    protected $actionKernel;
    /**
     * @var \Phruts\Action\ActionMapping
     */
    protected $mapping;
    /**
     * @var Symfony\Component\HttpFoundation\Request
     */
    protected $request;
    /**
     * @var Symfony\Component\HttpFoundation\Response
     */
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

        $this->actionKernel = $this->getMockBuilder('\Phruts\Action\ActionKernel')
            ->disableOriginalConstructor()
            ->getMock();

        $this->actionKernel
            ->expects($this->any())
            ->method('getInternal')
            ->willReturn($internal);

        $this->action = new ActionDispatcher();
        $this->action->setActionKernel($this->actionKernel);

        $this->mapping = new ActionConfig();

        $this->request = new Request();
        $this->response = new Response();
    }

    public function testInstantiate()
    {
        $actionDispatcher = new \Phruts\Actions\ActionDispatcher();
        $this->assertTrue($actionDispatcher instanceof \Phruts\Action);
    }

    public function testMissingParam()
    {
        $this->setExpectedException('\Phruts\Exception');
        $this->action->execute($this->mapping, null, $this->request, $this->response);
    }

    public function testReservedParamPerform()
    {
        $this->mapping->setParameter('key');
        $this->request->initialize(array('key' => 'perform'));
        $this->setExpectedException('\Phruts\Exception');
        $this->action->execute($this->mapping, null, $this->request, $this->response);
    }

    public function testReservedParamExecute()
    {
        $this->mapping->setParameter('key');
        $this->request->initialize(array('key' => 'execute'));
        $this->setExpectedException('\Phruts\Exception');
        $this->action->execute($this->mapping, null, $this->request, $this->response);
    }

    public function testNoDispatch()
    {
        $this->mapping->setParameter('key');
        $this->request->initialize(array('key' => 'nomethodhere'));
        $this->action->execute($this->mapping, null, $this->request, $this->response);
        $this->assertNotEmpty($this->response->getContent());
        $this->assertEquals(500, $this->response->getStatusCode());
    }

    public function testUnspecified()
    {
        $this->mapping->setParameter('missing');
        $this->action->execute($this->mapping, null, $this->request, $this->response);
        $this->assertNotEmpty($this->response->getContent());
        $this->assertEquals(400, $this->response->getStatusCode());
    }
}