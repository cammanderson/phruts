<?php
/*
 * Author; Cameron Manderson <cameronmanderson@gmail.com>
 */

class RequestProcessorTest extends \PHPUnit_Framework_TestCase
{
    protected $requestProcessor;
    protected $request;
    protected $response;
    protected $moduleConfig;
    protected $actionKernel;
    protected $application;

    public function setUp()
    {

        $this->request = new \Symfony\Component\HttpFoundation\Request();
        $storage = new  Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage();
        $session = new Symfony\Component\HttpFoundation\Session\Session($storage);
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

    protected static function getMethod($name)
    {
        $class = new \ReflectionClass('\Phruts\Action\RequestProcessor');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
}
 