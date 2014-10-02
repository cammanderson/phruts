<?php
namespace ActionsTest;

use Phruts\Action\ActionMapping;
use Phruts\Actions\ForwardAction;
use Phruts\Config\ForwardConfig;
use Phruts\Config\ModuleConfig;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ForwardActionTest extends \PHPUnit_Framework_TestCase
{
    public function testInstantiate()
    {
        $action = new \Phruts\Actions\ForwardAction();
        $this->assertTrue($action instanceof \Phruts\Action\Action);
    }

    public function testForward()
    {
        $actionConfig = new ActionMapping();
        $actionConfig->setPath('/test');
        $actionConfig->setParameter('success');
        $moduleConfig = new ModuleConfig('');
        $moduleConfig->addActionConfig($actionConfig);
        $actionConfig->setModuleConfig($moduleConfig);

        $forwardConfigOrig = new ForwardConfig();
        $forwardConfigOrig->setName('success');
        $actionConfig->addForwardConfig($forwardConfigOrig);

        $action = new ForwardAction();
        $request = new Request();
        $response = new Response();

        $forwardConfig = $action->execute($actionConfig, null, $request, $response);

        $this->assertNotEmpty($forwardConfig);
        $this->assertEquals($forwardConfigOrig->getName(), $forwardConfig->getName());
    }

    public function testCreateForward()
    {
        $actionConfig = new ActionMapping();
        $actionConfig->setPath('/test');
        $actionConfig->setParameter('myfile.html');
        $moduleConfig = new ModuleConfig('');
        $moduleConfig->addActionConfig($actionConfig);
        $actionConfig->setModuleConfig($moduleConfig);

        $action = new ForwardAction();
        $request = new Request();
        $response = new Response();

        $forwardConfig = $action->execute($actionConfig, null, $request, $response);

        $this->assertNotEmpty($forwardConfig);
        $this->assertEquals($actionConfig->getParameter(), $forwardConfig->getPath());
    }

    public function testEmptyParameter()
    {
        $actionConfig = new ActionMapping();
        $actionConfig->setPath('/test');
        // NO PARAM
        $moduleConfig = new ModuleConfig('');
        $moduleConfig->addActionConfig($actionConfig);
        $actionConfig->setModuleConfig($moduleConfig);

        $action = new ForwardAction();
        $request = new Request();
        $response = new Response();

        $this->setExpectedException('\Phruts\Exception');
        $action->execute($actionConfig, null, $request, $response);
    }
}
 