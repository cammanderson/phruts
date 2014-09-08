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

        $this->application = new \Silex\Application();
        $this->actionKernel = new \Phruts\Action\ActionKernel($this->application);

        $this->requestProcessor = new \Phruts\RequestProcessor();
        $this->requestProcessor->init($this->actionKernel, $this->moduleConfig);

    }


    public function testProcessLocale()
    {
        
    }
}
 