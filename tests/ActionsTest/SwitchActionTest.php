<?php
namespace ActionsTest;

use Phruts\Action\ActionKernel;
use Phruts\Config\ActionConfig;
use Phruts\Config\ModuleConfig;
use Phruts\Util\Globals;
use Satooshi\Bundle\CoverallsBundle\Console\Application;
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
        $application = new \Silex\Application();
        $application[Globals::PREFIXES_KEY] = array();
        $application[Globals::MODULE_KEY] = new ModuleConfig('');
        $actionKernel = new ActionKernel($application);

        $this->action = new \Phruts\Actions\SwitchAction();
        $this->action->setActionKernel($actionKernel);
        $this->mapping = new ActionConfig();
        $this->request = Request::createFromGlobals();
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

    public function testSwitch()
    {
        $this->request->query->set('page', 'page');
        $this->request->query->set('prefix', 'invalid');
        $this->action->execute($this->mapping, null, $this->request, $this->response);
        // TODO: Check the module has switched
    }

}
 