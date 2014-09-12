<?php
namespace UtilTest;

use Phruts\Action\AbstractActionForm;
use Phruts\Action\ActionKernel;
use Phruts\Action\ActionMapping;
use Phruts\Config\FormBeanConfig;
use Phruts\Config\ModuleConfig;
use Phruts\Util\RequestUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestUtilTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var \Symfony\Component\HttpFoundation\Response
     */
    protected $response;

    public function setUp()
    {
        $this->request = new Request();
        $this->response = new Response();
    }

    public function testGetModuleName()
    {
        $application = new \Silex\Application();
        $application[\Phruts\Util\Globals::PREFIXES_KEY] = array('admin', 'other');

        $request = $this->getMock('\Symfony\Component\HttpFoundation\Request');
        $request->expects($this->any())->method('getPathInfo')->willReturn('admin/example');
        $this->assertEquals('admin/example',$request->getPathInfo());
        $this->assertEquals('admin', RequestUtils::getModuleName($request, $application));

        $request = $this->getMock('\Symfony\Component\HttpFoundation\Request');
        $request->expects($this->any())->method('getPathInfo')->willReturn('example');
        $this->assertEquals('', RequestUtils::getModuleName($request, $application));

        $request = $this->getMock('\Symfony\Component\HttpFoundation\Request');
        $request->expects($this->any())->method('getPathInfo')->willReturn('other/example');
        $this->assertEquals('other', RequestUtils::getModuleName($request, $application));
    }

    public function testSelectModule()
    {
        // Setup a module prefix of ''
        $moduleConfig = new ModuleConfig('');

        $application = new \Silex\Application();
        $application[\Phruts\Util\Globals::PREFIXES_KEY] = array('admin', 'other');
        $application[\Phruts\Util\Globals::MODULE_KEY . RequestUtils::getModuleName($this->request, $application)] = $moduleConfig;

        RequestUtils::selectModule($this->request, $application);

        $this->assertNotEmpty($this->request->attributes->get(\Phruts\Util\Globals::MODULE_KEY));
        // TODO: Test that the messages is assigned

        $this->assertNotEmpty(RequestUtils::getModuleConfig($this->request, $application));
    }

    public function testCreateActionForm()
    {
        $moduleConfig = new ModuleConfig('');
        $application = new \Silex\Application();
        $actionMapping = new ActionMapping();
        $actionMapping->setName('form');
        $actionMapping->setModuleConfig($moduleConfig);
        $actionMapping->setScope('request');
        $actionKernel = new ActionKernel($application);
        $formBeanConfig = new FormBeanConfig();
        $formBeanConfig->setName('form');
        $formBeanConfig->setType('\UtilTest\MyActionForm');
        $moduleConfig->addFormBeanConfig($formBeanConfig);
        $moduleConfig->addActionConfig($actionMapping);

        $actionMapping->setAttribute('attribute');
        $form = RequestUtils::createActionForm($this->request, $actionMapping, $moduleConfig, $actionKernel);
        $this->assertNotEmpty($form);
        $this->assertEquals('UtilTest\MyActionForm', get_class($form));
    }

    public function testPopulate()
    {
        $this->request->initialize(array('valueOne' => 'value1', 'valueTwo' => 'value2', 'myValue' => 'myResult'));

        $bean = new MyBean();
        RequestUtils::populate($bean, '', '', $this->request);
        $this->assertEquals('value1', $bean->valueOne);

        $bean = new MyBean();
        RequestUtils::populate($bean, 'value', '', $this->request);
        $this->assertEquals('value1', $bean->One);
        $this->assertEquals('value2', $bean->Two);
        $this->assertEmpty($bean->myValue);

        $bean = new MyBean();
        RequestUtils::populate($bean, '', 'Value', $this->request);
        $this->assertEquals('myResult', $bean->my);
        $this->assertEmpty($bean->valueOne);

        $this->setExpectedException('\Exception');
        RequestUtils::populate(null, '', '', $this->request);
    }

    public function testRetrieveMessageResources()
    {
        $application = new \Silex\Application();

        $moduleConfig = new ModuleConfig('');



        $application[\Phruts\Util\Globals::PREFIXES_KEY] = array();
        $application[\Phruts\Util\Globals::MODULE_KEY . RequestUtils::getModuleName($this->request, $application)] = $moduleConfig;

        RequestUtils::selectModule($this->request, $application);

        $this->assertEmpty(RequestUtils::retrieveMessageResources($this->request, $application, null));

        $application[\Phruts\Util\Globals::MESSAGES_KEY] = 'PMR';
        $this->assertNotEmpty(RequestUtils::retrieveMessageResources($this->request, $application, null));
    }


    public function testRetrieveUserLocale()
    {
        // TODO:
    }

    public function testRequestToServerStringBuffer()
    {
        $this->request->initialize(array(), array(), array(), array(), array(), array('HTTP_PORT' => 80, 'HTTP_HOST' => 'localhost') );
        $this->assertEquals('http://localhost', RequestUtils::requestToServerStringBuffer($this->request));

        $this->request->initialize(array(), array(), array(), array(), array(), array('HTTP_PORT' => 443, 'HTTP_HOST' => 'github.com', 'HTTPS' => 'on') );
        $this->assertEquals('https://github.com', RequestUtils::requestToServerStringBuffer($this->request));
    }





}

class MyBean
{
    public $One;
    public $Two;
    public $my;
    public $Value;
    public $myValue;
    public $valueOne;
    public $valueTwo;
}


class MyActionForm extends AbstractActionForm
{}
 