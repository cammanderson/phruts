<?php
namespace ActionTest;

use Phruts\Action\ActionMapping;
use Phruts\Config\ActionConfig;

class RequestProcessorTest extends \PHPUnit_Framework_TestCase
{
    protected $requestProcessor;
    protected $request;
    protected $response;

    /**
     * @var \Phruts\Config\ModuleConfig
     */
    protected $moduleConfig;

    /**
     * @var \Phruts\Action\ActionMapping
     */
    protected $actionConfig1;

    protected $actionKernel;
    protected $application;

    public function setUp()
    {

        $this->request = new \Symfony\Component\HttpFoundation\Request();
        $storage = new \Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage();
        $session = new \Symfony\Component\HttpFoundation\Session\Session($storage);
        $this->request->setSession($session);
        $this->response = new \Symfony\Component\HttpFoundation\Response();


        $this->moduleConfig = new \Phruts\Config\ModuleConfig('default');
        $actionConfig = new \Phruts\Config\ActionConfig();
        $actionConfig->setPath('/default');
        $actionConfig->setType('\Phruts\Actions\ForwardAction');
        $this->moduleConfig->addActionConfig($actionConfig);

        $controllerConfig = new \Phruts\Config\ControllerConfig();
        $controllerConfig->setLocale('fr');
        $controllerConfig->setContentType('application/x-javascript');
        $this->moduleConfig->setControllerConfig($controllerConfig);

        // Add a default action mapping
        $this->actionConfig1 = new ActionMapping();
        $this->actionConfig1->setPath('/mypath');
        $this->actionConfig1->setModuleConfig($this->moduleConfig);
        $this->moduleConfig->addActionConfig($this->actionConfig1);

        $this->application = new \Silex\Application();
        $this->actionKernel = new \Phruts\Action\ActionKernel($this->application);

        $this->requestProcessor = new \Phruts\Action\RequestProcessor();
        $this->requestProcessor->init($this->actionKernel, $this->moduleConfig);

    }


    public function testProcessLocale()
    {
        $method = self::getMethod('processLocale');

        $method->invokeArgs($this->requestProcessor, array($this->request, $this->response));
        $this->assertEquals('en', $this->request->getSession()->get(\Phruts\Globals::LOCALE_KEY));
    }

    public function testProcessContent()
    {
        $method = self::getMethod('processContent');

        $method->invokeArgs($this->requestProcessor, array($this->request, $this->response));
        $this->assertEquals($this->moduleConfig->getControllerConfig()->getContentType(), $this->response->getContent());
    }

    public function testProcessException()
    {
        $method = self::getMethod('processException');

        $exception = new \Exception();
        $form = null;
        $mapping = $this->actionConfig1;

        $this->setExpectedException('\Exception');
        $method->invokeArgs($this->requestProcessor, array($this->request, $this->response, $exception, $form, $mapping));
    }

    public function testProcessNoCache()
    {
        $method = self::getMethod('processNoCache');

        $method->invokeArgs($this->requestProcessor, array($this->request, $this->response));
    }

    public function testProcessPreprocess()
    {
        $method = self::getMethod('processPreprocess');

        $method->invokeArgs($this->requestProcessor, array($this->request, $this->response));
    }

    public function testProcessMapping()
    {
        $method = self::getMethod('processMapping');

        $mapping = $this->actionConfig1;

        $method->invokeArgs($this->requestProcessor, array($this->request, $this->response, $mapping));
    }

    public function testProcessRoles()
    {
        $method = self::getMethod('processRoles');

        $mapping = $this->actionConfig1;

        $method->invokeArgs($this->requestProcessor, array($this->request, $this->response, $mapping));
    }

    public function testProcessActionForm()
    {
        $method = self::getMethod('processActionForm');

        $mapping = $this->actionConfig1;

        $method->invokeArgs($this->requestProcessor, array($this->request, $this->response, $mapping));
    }

    public function testProcessPopulate()
    {
        $method = self::getMethod('processPopulate');

        $mapping = $this->actionConfig1;
        $form = null;

        $method->invokeArgs($this->requestProcessor, array($this->request, $this->response, $form, $mapping));
    }

    public function testProcessValidate()
    {
        $method = self::getMethod('processValidate');

        $mapping = $this->actionConfig1;
        $form = null;

        $method->invokeArgs($this->requestProcessor, array($this->request, $this->response, $form, $mapping));
    }

    protected static function getMethod($name)
    {
        $class = new \ReflectionClass('\Phruts\Action\RequestProcessor');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

}
 